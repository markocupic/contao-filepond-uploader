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

        $tmpPath = Path::join($this->projectDir, $this->tmpPath, $transferKey);

        if (!is_dir($tmpPath)) {
            return null;
        }

        $finder = new Finder();
        $results = iterator_to_array($finder->files()->in($tmpPath));

        // Each folder contains only one file
        return $results[array_key_first($results)] ?? null;
    }

    /**
     * Returns an array with all the information per file that Contao expects for the widget's value.
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
}
