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

use Markocupic\ContaoFilepondUploader\Upload\FileUploader;
use Markocupic\ContaoFilepondUploader\Widget\FilepondFrontendWidget;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

#[Autoconfigure(public: true)]
readonly class Validator
{
    public function __construct(
        private FileUploader $fileUploader,
        #[Autowire('%kernel.project_dir%')]
        private string $projectDir,
    ) {
    }

    /**
     * Validate the widget input.
     */
    public function validateInput(FilepondFrontendWidget $widget, array|string|null $varInput): array|string
    {
        // No input
        if (empty($varInput)) {
            return $this->validateEmptyValue($widget);
        }

        // If the "multiple" attribute is set,
        // FilePond submits the input as "array", otherwise as "string".
        if (\is_array($varInput)) {
            return $this->validateMultipleFiles($widget, array_filter($varInput));
        }

        return $this->validateSingleFile($widget, $varInput);
    }

    public function isUuid(string $value): bool
    {
        return \Contao\Validator::isUuid($value);
    }

    /**
     * Validate an empty value.
     */
    private function validateEmptyValue(FilepondFrontendWidget $widget): array|string
    {
        // Add an error if the field is mandatory
        if ($widget->mandatory) {
            if ($widget->label) {
                $widget->addError(\sprintf($GLOBALS['TL_LANG']['ERR']['mandatory'], $widget->label));
            } else {
                $widget->addError($GLOBALS['TL_LANG']['ERR']['mdtryNoLabel']);
            }
        }

        $config = $widget->getUploaderConfig();

        return $config->isMultiple() ? [] : '';
    }

    /**
     * Validate the single file.
     */
    private function validateSingleFile(FilepondFrontendWidget $widget, string $varInput): string
    {
        try {
            // Returns the UUID of the uploaded file if addToDbafs is set to true,
            // otherwise the relative path to the uploaded file.
            $pathOrUuid = $this->fileUploader->storeFile($widget->getUploaderConfig(), $varInput);

            return base64_encode($pathOrUuid);
        } catch (\Exception $e) {
            $widget->addError($GLOBALS['TL_LANG']['ERR']['emptyUpload']);
        }

        return $varInput;
    }

    /**
     * Validate multiple files.
     *
     * @return array<string>
     */
    private function validateMultipleFiles(FilepondFrontendWidget $widget, array $varInputs): array
    {
        $config = $widget->getUploaderConfig();

        // Limit the number of uploads
        if ($config->getFileLimit() > 0) {
            $varInputs = \array_slice($varInputs, 0, $config->getFileLimit());
        }

        $inputs = [];

        // Store the files
        foreach ($varInputs as $k => $splFileInfo) {
            try {
                // Returns the UUID of the uploaded file if addToDbafs is set to true,
                // otherwise the relative path to the uploaded file.
                $pathOrUuid = $this->fileUploader->storeFile($config, $splFileInfo);
                $inputs[$k] = base64_encode($pathOrUuid);
            } catch (\Exception $e) {
                $widget->addError($GLOBALS['TL_LANG']['ERR']['emptyUpload']);
            }
        }

        return $inputs;
    }
}
