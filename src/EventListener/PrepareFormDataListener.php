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
use Markocupic\ContaoFilepondUploader\Widget\FrontendWidget;

#[AsHook('prepareFormData')]
class PrepareFormDataListener
{
    public function __invoke(array &$submittedData, array $labels, array $fields, Form $form, ?array &$files = null): void
    {
        /** @var FormFieldModel $model */
        foreach ($fields as $name => $model) {
            if (FrontendWidget::TYPE !== $model->type) {
                continue;
            }

            if (isset($submittedData[$name])) {
                $submittedData[$name] = array_map(static fn (array $file): string => $file['tmp_name'], $submittedData[$name]);
            }

            $filesNew = [];

            if (isset($files[$name])) {
                foreach ($files[$name] as $file) {
                    $filesNew[] = $file;
                }

                $files[$name] = $filesNew;
            }
        }
    }
}
