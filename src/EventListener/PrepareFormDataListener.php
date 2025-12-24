<?php

declare(strict_types=1);

/*
 * This file is part of Contao Filepond Uploader.
 *
 * (c) Marko Cupic <m.cupic@gmx.ch>
 * @license MIT
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 * @link https://github.com/markocupic/contao-filepond-uploader
 */

namespace Markocupic\ContaoFilepondUploader\EventListener;

use Contao\CoreBundle\DependencyInjection\Attribute\AsHook;
use Contao\Form;
use Contao\FormFieldModel;
use Markocupic\ContaoFilepondUploader\Widget\FilepondFrontendWidget;

#[AsHook('prepareFormData')]
readonly class PrepareFormDataListener
{
    public function __invoke(array &$submittedData, array $labels, array $fields, Form $form, ?array &$files = null): void
    {
        /** @var FormFieldModel $model */
        foreach ($fields as $name => $model) {
            if (FilepondFrontendWidget::TYPE !== $model->type) {
                continue;
            }

            $this->transformFilepondSubmittedData($submittedData, $name);
            $this->normalizeFilesArray($files, $name);
        }
    }

    /**
     * Transforms the Filepond file structure by extracting only the tmp_name values.
     */
    private function transformFilepondSubmittedData(array &$submittedData, string $fieldName): void
    {
        if (!isset($submittedData[$fieldName])) {
            return;
        }
        $submittedData[$fieldName] = array_map(
            static fn (array $file): string => $file['tmp_name'],
            $submittedData[$fieldName],
        );
    }

    /**
     * Normalizes the "files" array by re-indexing the entries.
     */
    private function normalizeFilesArray(?array &$files, string $fieldName): void
    {
        if (!isset($files[$fieldName])) {
            return;
        }

        $files[$fieldName] = array_values($files[$fieldName]);
    }
}
