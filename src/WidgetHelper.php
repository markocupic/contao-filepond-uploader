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

use Contao\FilesModel;
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
        private TransferKey $transferKey,
        #[Autowire('%kernel.project_dir%')]
        private string $projectDir,
        #[Autowire('%markocupic_contao_filepond_uploader.tmp_path%')]
        private string $tmpPath,
    ) {
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

            $result = $this->getFileFromTransferKey($transferKey);

            if (null === $result) {
                continue;
            }

            $return[] = $result;
        }

        return $return;
    }

    public function getFileFromTransferKey(string $transferKey): string|null
    {
        if ('' === $transferKey) {
            return null;
        }

        $transferKey = base64_decode($transferKey, true);

        // 1) File was uploaded directly and added to the DBAFS -> return the UUID
        if (Validator::isUuid($transferKey) && null !== FilesModel::findByUuid($transferKey)) {
            return $transferKey;
        }

        // 2) File was uploaded directly but not added to the DBAFS -> return the relative path
        $absolutePath = Path::makeAbsolute($transferKey, $this->projectDir);

        if (is_file($absolutePath)) {
            return $transferKey;
        }

        // 3) Invalid FilePond transfer key -> return null
        if (!$this->transferKey->validate($transferKey)) {
            return null;
        }

        $tmpPath = Path::join($this->projectDir, $this->tmpPath, $transferKey);

        if (!is_dir($tmpPath)) {
            return null;
        }

        // 4) Each folder contains only one file → return the file path of the first match
        $finder = (new Finder())->files()->in($tmpPath);
        $file = current(iterator_to_array($finder));

        return $file ? $file->getRealPath() : null;
    }

    /**
     * Returns an array with all the information per file that Contao expects for the widget's value.
     */
    public function getFilesArray(string $name, array $files, bool $storeFile = false): array
    {
        $count = 0;
        $arrFiles = [];

        foreach ($files as $file) {
            $model = null;

            // Get the file model
            if (Validator::isUuid($file)) {
                if (null === ($model = FilesModel::findByUuid($file))) {
                    continue;
                }

                $filePath = Path::makeAbsolute($model->path, $this->projectDir);
            } else {
                // If the file path is absolute.
                $filePath = $file;
            }

            if (!is_file($filePath)) {
                continue;
            }

            $file = new \SplFileInfo($filePath);

            $key = $name.'_'.$count++;

            $arrFile = [
                'name' => $file->getBasename(),
                'type' => $file->getMTime(),
                'tmp_name' => $file->getRealPath(), // Must be absolute and inside the project directory
                'error' => 0,
                'size' => $file->getSize(),
                'uuid' => null !== $model ? StringUtil::binToUuid($model->uuid) : '',
            ];

            // Only set the 'uploaded' key if we store the file (https://github.com/contao/contao/pull/7039)
            if ($storeFile) {
                $arrFile['uploaded'] = true;
            }

            $arrFiles[$key] = $arrFile;
        }

        return $arrFiles;
    }
}
