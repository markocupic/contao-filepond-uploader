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
    public function __invoke(array &$submittedData, array $labels, array $fields, Form $form, ?array &$arrFiles = null): void
    {
        /** @var FormFieldModel $model */
        foreach ($fields as $name => $model) {
            if (FilepondFrontendWidget::TYPE !== $model->type) {
                continue;
            }

            if (isset($submittedData[$name])) {
                if (1 === \count($submittedData[$name])) {
                    $submittedData[$name] = reset($submittedData[$name]);
                } else {
                    $submittedData[$name] = array_map(static fn (array $file): string => $file['tmp_name'], $submittedData[$name]);
                }
            }

            if (isset($arrFiles[$name])) {
                if (1 === \count($arrFiles[$name])) {
                    $arrFiles[$name] = reset($arrFiles[$name]);
                } else {
                    foreach ($arrFiles[$name] as $key => $file) {
                        $arrFiles[$key] = $file;
                    }

                    unset($arrFiles[$name]);
                }
            }
        }
    }
}
