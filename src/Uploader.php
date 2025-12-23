<?php

declare(strict_types=1);

/*
 * This file is part of Contao Filepond Uploader.
 *
 * (c) Marko Cupic 2024 <m.cupic@gmx.ch>
 * @license GPL-3.0-or-later
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 * @link https://github.com/markocupic/contao-filepond-uploader
 */

namespace Markocupic\ContaoFilepondUploader;

use Contao\Config;
use Contao\Dbafs;
use Contao\StringUtil;
use Contao\Validator;
use Markocupic\ContaoFilepondUploader\Widget\BaseWidget;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Filesystem\Path;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class Uploader
{
    public function __construct(
        private readonly ChunkUploader $chunkUploader,
        private readonly Filesystem $fs,
        private readonly RequestStack $requestStack,
        #[Autowire('%kernel.project_dir%')]
        private readonly string $projectDir,
    ) {
    }

    /**
     * Upload the file.
     */
    public function upload(Request $request, BaseWidget $widget): array|null
    {
        $uploader = new FileUpload($widget->name);
        $config = $widget->getUploaderConfig();
        $isChunk = $config->isChunkingEnabled() && $request->request->has('qqpartindex');

        // Convert the $_FILES array to Contao format
        $this->convertGlobalFilesArray($request, $widget, $isChunk);

        // Configure the uploader
        $this->configureUploader($uploader, $config, $isChunk);

        // Run the upload
        if (null === ($result = $this->runUpload($uploader, $widget, $request->attributes->get('_scope')))) {
            return null;
        }

        $filePath = $result[0];

        // Handle the chunk
        if ($isChunk) {
            $filePath = $this->chunkUploader->handleChunk($request, $widget, $filePath);
            $isChunk = !$this->chunkUploader->isLastChunk($request);
        }

        // Validate and move the file immediately
        if ($config->isDirectUploadEnabled() && !$isChunk) {
            $filePath = $this->storeFile($config, $filePath);
        }

        return [
            'filePath' => $filePath,
            'transferKey' => $uploader->getTransferKey(),
        ];
    }

    /**
     * Store a single file.
     */
    public function storeFile(UploaderConfig $config, string $file): string
    {
        // Convert uuid to binary format
        if (Validator::isStringUuid($file)) {
            $file = StringUtil::uuidToBin($file);
        } elseif ($this->fs->fileExists($file)) {
            // Move the temporary file
            if ($config->isStoreFileEnabled() && $config->getUploadFolder()) {
                $file = $this->fs->moveTmpFile($file, $config->getUploadFolder(), $config->isDoNotOverwriteEnabled());

                // Add the file to database file system
                if ($config->isAddToDbafsEnabled() && null !== ($model = Dbafs::addResource($file))) {
                    $file = $model->uuid;
                }
            }
        } else {
            // The file does not exist
            throw new \Exception(\sprintf('The file "%s" does not exist', $file));
        }

        return $file;
    }

    /**
     * Run the upload.
     */
    private function runUpload(FileUpload $uploader, BaseWidget $widget, string $scope): array|null
    {
        $result = null;

        $targetPath = Path::join($this->fs->getTmpPath(), $uploader->getTransferKey());

        $this->createTargetFolderIfNotExists($targetPath);

        try {
            $result = $uploader->uploadTo($targetPath);

            // Collect the errors
            if ($uploader->hasError()) {
                $errors = $this->requestStack->getSession()->getFlashBag()->peek(\sprintf('contao.%s.error', $scope));

                foreach ($errors as $error) {
                    $widget->addError($error);
                }
            }

            $this->requestStack->getSession()->getFlashBag()->clear();
        } catch (\Exception $e) {
            $widget->addError($e->getMessage());
        }

        // Add an error if the result is incorrect
        if (!\is_array($result) || \count($result) < 1) {
            $widget->addError($GLOBALS['TL_LANG']['MSC']['fineuploader.error']);
            $result = null;
        }

        return $result;
    }

    private function createTargetFolderIfNotExists(string $strPath): void
    {
        $fs = new \Symfony\Component\Filesystem\Filesystem();
        $strPath = Path::makeAbsolute($strPath, $this->projectDir);
        $fs->mkdir($strPath, Config::get('defaultFolderChmod'));
    }

    /**
     * Configure the uploader.
     */
    private function configureUploader(FileUpload $uploader, UploaderConfig $config, bool $isChunk): void
    {
        // Add the "chunk" extension to upload types
        if ($isChunk) {
            $uploader->setExtensions(['chunk']);
        }

        // Set the minimum size limit
        if ($config->getMinSizeLimit() > 0 && !$isChunk) {
            $uploader->setMinFileSize($config->getMinSizeLimit());
        }

        // Set the maximum file or chunk size
        if ($config->getMaxSizeLimit() > 0 || $isChunk) {
            $uploader->setMaxFileSize($isChunk ? $config->getChunkSize() : $uploader->getMaxFileSize());
        }

        // Set the maximum image width
        if ($config->getMaxImageWidth() > 0 && !$isChunk) {
            $uploader->setImageWidth($config->getMaxImageWidth());
        }

        // Set the maximum image height
        if ($config->getMaxImageHeight() > 0 && !$isChunk) {
            $uploader->setImageHeight($config->getMaxImageHeight());
        }
    }

    /**
     * Convert the global files array to Contao format.
     */
    private function convertGlobalFilesArray(Request $request, BaseWidget $widget, bool $isChunk): void
    {
        $name = $widget->name;

        if (empty($_FILES[$name])) {
            return;
        }

        $file = $_FILES[$name];

        // Replace the special characters (#22)
        $file['name'][0] = $this->fs->standardizeFileName($file['name'][0]);

        // Set the UUID as the filename
        if ($isChunk) {
            $file['name'][0] = $request->request->get('qquuid').'.chunk';
        }

        // Check if the file exists
        if ($this->fs->tmpFileExists($file['name'][0])) {
            $file['name'][0] = $this->fs->getTmpFileName($file['name'][0]);
        }

        $_FILES[$widget->name] = $file;
        // unset($_FILES[$name]); // Unset the temporary file
    }
}
