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

#[Autoconfigure(public: true)]
readonly class Validator
{
    public function __construct(
        private FileUploader $fileUploader,
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
            // Store the file in the target folder
            // Returns the UUID of the uploaded file if addToDbafs is set to true,
            // otherwise the relative path to the uploaded file.
            return $this->fileUploader->storeFile($widget->getUploaderConfig(), $varInput);
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

        // Store the files in the target folder
        foreach ($varInputs as $relPathOrUuid) {
            try {
                // Returns the UUID of the uploaded file if addToDbafs is set to true,
                // otherwise the relative path to the uploaded file.
                $inputs[] = $this->fileUploader->storeFile($config, $relPathOrUuid);
            } catch (\Exception $e) {
                $widget->addError($GLOBALS['TL_LANG']['ERR']['emptyUpload']);
            }
        }

        return $inputs;
    }
}
