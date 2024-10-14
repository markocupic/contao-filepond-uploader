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

use Contao\Config;
use Contao\File;
use Contao\FilesModel;
use Contao\Validator;
use Markocupic\ContaoFilepondUploader\Event\FileUploadEvent;
use Markocupic\ContaoFilepondUploader\Uploader;
use Markocupic\ContaoFilepondUploader\Widget\FrontendWidget;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\JsonResponse;

class FileUploadListener
{
    public function __construct(
        private readonly Uploader $uploader,
    ) {
    }

    /**
     * On file upload.
     */
    #[AsEventListener]
    public function onFileUpload(FileUploadEvent $event): void
    {
        $widget = $event->getWidget();
        $arrUploadResult = $this->uploader->upload($event->getRequest(), $widget);

        if (null === $arrUploadResult) {
            $event->setResponse(
                new JsonResponse([
                    'success' => false,
                    'filePondItemId' => $event->getRequest()->attributes->get('filePondItemId'),
                    'error' => $GLOBALS['TL_LANG']['ERR']['general'],
                    'preventRetry' => true,
                ])
            );

            return;
        }

        $filePath = $arrUploadResult['filePath'];

        if (Validator::isUuid($filePath)) {
            $fileModel = FilesModel::findByUuid($filePath);

            if (null === $fileModel) {
                $event->setResponse(
                    new JsonResponse([
                        'success' => false,
                        'filePondItemId' => $event->getRequest()->attributes->get('filePondItemId'),
                        'error' => $GLOBALS['TL_LANG']['ERR']['general'],
                        'preventRetry' => true,
                    ])
                );

                return;
            }

            $filePath = $fileModel->path;
        }

        // Validate the image dimensions for the frontend widget
        if ($widget instanceof FrontendWidget) {
            $this->validateImageDimensions($widget, $filePath);
        }

        if ($widget->hasErrors()) {
            $response = [
                'success' => false,
                'filePondItemId' => $event->getRequest()->attributes->get('filePondItemId'),
                'error' => $widget->getErrorAsString(),
                'preventRetry' => true,
            ];
        } else {
            // Everything ok! Send the transfer key to the client.
            $response = [
                'success' => true,
                'filePondItemId' => $event->getRequest()->attributes->get('filePondItemId'),
                'error' => null,
                'transferKey' => $arrUploadResult['transferKey'],
            ];
        }

        $event->setResponse(new JsonResponse($response, 200));
    }

    /**
     * Validate the image dimensions.
     */
    private function validateImageDimensions(FrontendWidget $widget, string $filePath): void
    {
        $file = new File($filePath);

        if ($file->isImage) {
            $config = $widget->getUploaderConfig();

            $minWidth = $config->getMinImageWidth() ?: 0;
            $minHeight = $config->getMinImageHeight() ?: 0;
            $maxWidth = $config->getMaxImageWidth() ?: Config::get('imageWidth');
            $maxHeight = $config->getMaxImageHeight() ?: Config::get('imageHeight');

            // Image width is smaller than the minimum image width
            if ($minWidth > 0 && $file->width < $minWidth) {
                $widget->addError(sprintf($GLOBALS['TL_LANG']['ERR']['fileminwidth'], '', $minWidth));
            }

            // Image height is smaller than the minimum image height
            if ($minHeight > 0 && $file->height < $minHeight) {
                $widget->addError(sprintf($GLOBALS['TL_LANG']['ERR']['fileminheight'], '', $minHeight));
            }

            // Image exceeds maximum image width
            if ($maxWidth > 0 && $file->width > $maxWidth) {
                $widget->addError(sprintf($GLOBALS['TL_LANG']['ERR']['filewidth'], '', $maxWidth));
            }

            // Image exceeds maximum image height
            if ($maxHeight > 0 && $file->height > $maxHeight) {
                $widget->addError(sprintf($GLOBALS['TL_LANG']['ERR']['fileheight'], '', $maxHeight));
            }
        }
    }
}
