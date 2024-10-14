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

use Markocupic\ContaoFilepondUploader\Widget\BaseWidget;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;

#[Autoconfigure(public: true)]
class Validator
{
    public function __construct(
        private readonly Uploader $uploader,
    ) {
    }

    /**
     * Validate the widget input.
     */
    public function validateInput(BaseWidget $widget, array|string $input): array|string
    {
        // No input
        if (empty($input)) {
            return $this->validateEmptyValue($widget);
        }

        // If "multiple" is set the input type "array", otherwise "string".
        if (\is_array($input)) {
            return $this->validateMultipleFiles($widget, array_filter($input));
        }

        return $this->validateSingleFile($widget, $input);
    }

    /**
     * Validate an empty value.
     */
    private function validateEmptyValue(BaseWidget $widget): array|string
    {
        // Add an error if the field is mandatory
        if ($widget->mandatory) {
            if ($widget->label) {
                $widget->addError(sprintf($GLOBALS['TL_LANG']['ERR']['mandatory'], $widget->label));
            } else {
                $widget->addError($GLOBALS['TL_LANG']['ERR']['mdtryNoLabel']);
            }
        }

        return $widget->multiple ? [] : '';
    }

    /**
     * Validate the single file.
     */
    private function validateSingleFile(BaseWidget $widget, string $input): string
    {
        try {
            return $this->uploader->storeFile($widget->getUploaderConfig(), $input);
        } catch (\Exception $e) {
            $widget->addError($GLOBALS['TL_LANG']['ERR']['emptyUpload']);
        }

        return $input;
    }

    /**
     * Validate the multiple files.
     */
    private function validateMultipleFiles(BaseWidget $widget, array $inputs): array
    {
        $config = $widget->getUploaderConfig();

        // Limit the number of uploads
        if ($config->getFileLimit() > 0) {
            $inputs = \array_slice($inputs, 0, $config->getFileLimit());
        }

        // Store the files
        foreach ($inputs as $k => $v) {
            try {
                $inputs[$k] = $this->uploader->storeFile($config, $v);
            } catch (\Exception $e) {
                $widget->addError($GLOBALS['TL_LANG']['ERR']['emptyUpload']);
            }
        }

        return $inputs;
    }
}
