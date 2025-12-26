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

use Contao\File;
use Contao\FilesModel;
use Contao\Model\Collection;
use Contao\StringUtil;
use Contao\Validator;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Filesystem\Path;
use Symfony\Component\Finder\Finder;

#[Autoconfigure(public: true)]
readonly class WidgetHelper
{
    public function __construct(
        private Filesystem $fs,
        private TransferKey $transferKey,
        #[Autowire('%kernel.project_dir%')]
        private string $projectDir,
        #[Autowire('%markocupic_contao_filepond_uploader.tmp_path%')]
        private string $tmpPath,
    ) {
    }

    /**
     * Generate the value.
     */
    public function generateValue(array $value): array
    {
        if (\count($value) < 1) {
            return [];
        }

        $uuids = [];
        $tmpFiles = [];

        // Split the files into UUIDs and temporary ones
        foreach ($value as $file) {
            if (Validator::isBinaryUuid($file)) {
                $uuids[] = $file;
            } else {
                $tmpFiles[] = $file;
            }
        }

        // Get the database files
        $return = $this->generateDatabaseFiles($uuids);

        // Get the temporary files
        return array_merge($return, $this->generateTmpFiles($tmpFiles));
    }

    /**
     * Converts transferKeys from the file input field
     * to SplFileInfo objects and returns them as an array.
     *
     * @return array<string>
     */
    public function getFilesFromFileInputField(array|string|null $files): array
    {
        $files = (array) $files;

        $return = [];

        foreach ($files as $transferKey) {
            if ('' === $transferKey || 'undefined' === $transferKey) {
                continue;
            }

            if (!$this->transferKey->validate($transferKey)) {
                throw new \Exception('Invalid transferKey: '.$transferKey);
            }

            if (null === ($objSplFileInfo = $this->getFileFromTransferKey($transferKey))) {
                continue;
            }

            if (!is_file($objSplFileInfo->getRealPath())) {
                continue;
            }

            $return[] = $objSplFileInfo->getRealPath();
        }

        return $return;
    }

    public function getFileFromTransferKey(string $transferKey): \SplFileInfo|null
    {
        if ('' === $transferKey) {
            return null;
        }

        $finder = new Finder();
        $finder->files()->in(Path::join($this->projectDir, $this->tmpPath, $transferKey));

        // check if there are any search results
        if ($finder->hasResults()) {
            foreach ($finder as $file) {
                return $file;
            }
        }

        return null;
    }

    /**
     * Returns an array with all the information per file that Contao expects for the widget's value or the session value.
     */
    public function getFilesArray(string $name, array $files, bool|null $storeFile = null): array
    {
        $storeFile = $storeFile ?? true;
        $count = 0;
        $return = [];

        foreach ($files as $file) {
            $model = null;

            // Get the file model
            if (Validator::isUuid($file)) {
                if (null === ($model = FilesModel::findByUuid($file))) {
                    continue;
                }

                $filePath = $model->path;
            } else {
                // If the file path is absolute.
                $filePath = Path::makeRelative($file, $this->projectDir);
            }

            $file = new File($filePath);

            if (!$file->exists()) {
                continue;
            }

            $key = $name.'_'.$count++;

            $return[$key] = [
                'name' => $file->name,
                'type' => $file->mime,
                'tmp_name' => Path::join($this->projectDir, $file->path),
                'error' => 0,
                'size' => $file->size,
                'uuid' => null !== $model ? StringUtil::binToUuid($model->uuid) : '',
            ];

            // Only set the 'uploaded' key if we store the file (https://github.com/contao/contao/pull/7039)
            if ($storeFile) {
                $return[$key]['uploaded'] = true;
            }
        }

        return $return;
    }

    /**
     * Add the files to the session in order to reproduce Contao 4.13 uploader behavior.
     */
    public function addFilesToSession(string $name, array $files, bool $storeFile = true): void
    {
        $files = $this->getFilesArray($name, $files, $storeFile);

        foreach ($files as $name => $data) {
            $_SESSION['FILES'][$name] = $data;
        }
    }

    /**
     * Generate the database files.
     */
    private function generateDatabaseFiles(array $uuids): array
    {
        if (null === ($fileModels = FilesModel::findMultipleByUuids($uuids))) {
            return [];
        }

        $files = [];

        /**
         * @var Collection $fileModels
         * @var FilesModel $fileModel
         */
        foreach ($fileModels as $fileModel) {
            // Skip not-existing files
            if (!$this->fs->fileExists($fileModel->path)) {
                continue;
            }

            $files[StringUtil::binToUuid($fileModel->uuid)] = $fileModel->path;
        }

        return $files;
    }

    /**
     * Generate the temporary files.
     */
    private function generateTmpFiles(array $tmpFiles): array
    {
        $files = [];

        foreach ($tmpFiles as $file) {
            if (\is_array($file)) {
                $file = $file['tmp_name'] ?? null;
            }

            // Skip not-existing files
            if (!$file || !$this->fs->fileExists($file)) {
                continue;
            }

            $files[$file] = $file;
        }

        return $files;
    }
}
