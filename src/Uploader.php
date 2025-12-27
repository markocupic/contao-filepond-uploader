<?php

declare(strict_types=1);

/*
 * This file is part of Contao Filepond Uploader.
 *
 * (c) Marko Cupic <m.cupic@gmx.ch>
 * @license GPL-3.0-or-later
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 * @link https://github.com/markocupic/contao-filepond-uploader
 */

namespace Markocupic\ContaoFilepondUploader;

use Contao\Config;
use Contao\Dbafs;
use Markocupic\ContaoFilepondUploader\Widget\FilepondFrontendWidget;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Filesystem\Path;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;

readonly class Uploader
{
    public function __construct(
        private Filesystem $fs,
        private RequestStack $requestStack,
        #[Autowire('%kernel.project_dir%')]
        private string $projectDir,
    ) {
    }

    /**
     * Upload the file.
     */
    public function upload(FilepondFrontendWidget $widget): array|null
    {
        $uploader = new FileUpload($widget->name, $widget->getConfiguration());
        $config = $widget->getUploaderConfig();

        // Convert the $_FILES array to Contao format
        $this->prepareGlobalFilesArray($widget);

        // Configure the uploader
        $this->configureUploader($uploader, $config);

        // Run the upload
        if (null === ($result = $this->runUpload($uploader, $widget))) {
            return null;
        }

        $filePath = Path::makeAbsolute($result[0], $this->projectDir);

        // Validate and move the file immediately
        if ($config->isDirectUploadEnabled()) {
            // Returns the UUID of the uploaded file if addToDbafs is set to true,
            // otherwise the relative path to the uploaded file.
            $filePath = $this->storeFile($config, $filePath);
        }

        return [
            'filePath' => $filePath,
            'transferKey' => $uploader->getTransferKey(),
            'directUpload' => $config->isDirectUploadEnabled(),
        ];
    }

    /**
     * Store a single file.
     */
    public function storeFile(UploaderConfig $config, string $file): string
    {
        if (!is_file($file)) {
            // The file does not exist
            throw new \Exception(\sprintf('The file "%s" does not exist', $file));
        }

        $file = Path::makeRelative($file, $this->projectDir);

        // Move the temporary file
        if ($config->isStoreFileEnabled() && $config->getUploadFolder()) {
            $file = $this->fs->moveTmpFile($file, $config->getUploadFolder(), $config->isDoNotOverwriteEnabled());

            // Add the file to the database-assisted file system
            if ($config->isAddToDbafsEnabled() && null !== ($model = Dbafs::addResource($file))) {
                $file = $model->uuid;
            }
        }

        return $file;
    }

    /**
     * Run the upload.
     */
    private function runUpload(FileUpload $uploader, FilepondFrontendWidget $widget): array|null
    {
        $result = null;

        $targetPath = Path::join($this->fs->getTmpPath(), $uploader->getTransferKey());

        $this->createTargetFolderIfNotExists($targetPath);

        try {
            $result = $uploader->uploadTo($targetPath);

            // Collect the errors
            if ($uploader->hasError()) {
                /** @var Session $session */
                $session = $this->requestStack->getSession();
                $errors = $session->getFlashBag()->peek('contao.FE.error');

                foreach ($errors as $error) {
                    $widget->addError($error);
                }
            }

            /** @var Session $session */
            $session = $this->requestStack->getSession();

            $session->getFlashBag()->clear();
        } catch (\Exception $e) {
            $widget->addError($e->getMessage());
        }

        // Add an error if the result is incorrect
        if (!\is_array($result) || empty($result)) {
            $widget->addError($GLOBALS['TL_LANG']['ERR']['filepond.error']);
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
    private function configureUploader(FileUpload $uploader, UploaderConfig $config): void
    {
        // Set the minimum size limit
        if ($config->getMinSizeLimit() > 0) {
            $uploader->setMinFileSize($config->getMinSizeLimit());
        }

        // Set the maximum file or chunk size
        if ($config->getMaxSizeLimit() > 0) {
            $uploader->setMaxFileSize($uploader->getMaxFileSize());
        }

        // Set the maximum image width
        if ($config->getMaxImageWidth() > 0) {
            $uploader->setImageWidth($config->getMaxImageWidth());
        }

        // Set the maximum image height
        if ($config->getMaxImageHeight() > 0) {
            $uploader->setImageHeight($config->getMaxImageHeight());
        }
    }

    /**
     * Prepares the global $_FILES array for the given widget.
     *
     * This method modifies the global $_FILES array to standardize and set unique temporary file names
     * for file uploads based on the widget and request data.
     */
    private function prepareGlobalFilesArray(FilepondFrontendWidget $widget): void
    {
        $name = $widget->name;

        if (empty($_FILES[$name])) {
            return;
        }

        $files = $_FILES[$name];

        $filename = \is_array($files['name']) ? $files['name'][0] : $files['name'];
        $filename = $this->fs->standardizeFileName($filename);
        $filename = $this->fs->tmpFileExists($filename) ? $this->fs->getUniqueTmpFileName($filename) : $filename;

        // Check if the "multiple" attribute is set.
        if (\is_array($files['name'])) {
            $files['name'][0] = $filename;
        } else {
            $files['name'] = $filename;
        }

        unset($_FILES[$name]);

        $_FILES[$name] = $files;
    }
}
