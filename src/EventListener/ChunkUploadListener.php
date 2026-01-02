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

use Markocupic\ContaoFilepondUploader\Chunk\ChunkProcessor;
use Markocupic\ContaoFilepondUploader\Event\ChunkUploadEvent;
use Markocupic\ContaoFilepondUploader\Image\ImageResizer;
use Markocupic\ContaoFilepondUploader\Image\SvgSanitizer;
use Markocupic\ContaoFilepondUploader\Upload\FileUploader;
use Markocupic\ContaoFilepondUploader\Validator\Exception\NoFileUploadedException;
use Markocupic\ContaoFilepondUploader\Validator\Exception\TranslatableExceptionInterface;
use Markocupic\ContaoFilepondUploader\Validator\FileValidator;
use Markocupic\ContaoFilepondUploader\Validator\ImageValidator;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\Translation\TranslatorInterface;

readonly class ChunkUploadListener
{
    public function __construct(
        private ChunkProcessor $chunkProcessor,
        private FileUploader $fileUploader,
        private FileValidator $fileValidator,
        private ImageResizer $imageResizer,
        private ImageValidator $imageValidator,
        private SvgSanitizer $svgSanitizer,
        private TranslatorInterface $translator,
        #[Autowire('%kernel.debug%')]
        private bool $debug,
        private LoggerInterface|null $contaoErrorLogger,
    ) {
    }

    #[AsEventListener]
    public function onChunkUpload(ChunkUploadEvent $event): void
    {
        $this->chunkProcessor->cleanupOldChunks();

        $file = $event->getChunkFile();

        $uploadResult = $this->chunkProcessor->processChunk($file, $event->getWidget(), $event->getFileName(), $event->getFilePondItemId(), $event->getOffset(), $event->getTotalSize());

        if (!$uploadResult['completed'] || !$uploadResult['success']) {
            $response = new JsonResponse([
                'success' => true,
                'isChunk' => true,
                'offset' => $event->getOffset(),
                'totalSize' => $event->getTotalSize(),
                'fileName' => $event->getFileName(),
                'filePondItemId' => $event->getFilePondItemId(),
                'completed' => false,
            ]);

            $event->setResponse($response);

            return;
        }

        $widget = $event->getWidget();

        $uploadConfig = $widget->getUploaderConfig();

        $request = $event->getRequest();

        /** @var File $file */
        $file = $uploadResult['file'];

        try {
            $this->fileValidator->validateFileChecksum($file->getRealPath(), $event->getFileChecksum());

            $this->fileValidator->validateExtension($uploadResult['clientOriginalFileName'], $widget);

            $this->fileValidator->validateMinFileSize($file->getRealPath(), $widget);

            $this->fileValidator->validateMaxFileSize($file->getRealPath(), $widget);

            $extension = strtolower($file->getExtension());

            // Check if the uploaded svg-file contains malicious code
            if (\in_array(strtolower($extension), ['svg', 'svgz'], true) && !$this->svgSanitizer->sanitizeSvg($file->getRealPath())) {
                throw new NoFileUploadedException('The uploaded file is not a valid SVG.', 'ERR.fileerror');
            }

            // If the file is an image, resize it if necessary.
            if ($uploadConfig->isImageResizingEnabled()) {
                $this->imageResizer->resize($file->getRealPath(), $uploadConfig->getImageResizeWidth(), $uploadConfig->getImageResizeHeight());
            }

            // Validate the image dimensions for the frontend widget
            if ($this->imageValidator->isImage($file->getRealPath())) {
                $this->imageValidator->validateImageResolution($file->getRealPath(), $widget);
            }

            if ($uploadConfig->isStoreFileEnabled() && $uploadConfig->isDirectUploadEnabled()) {
                $pathOrUuid = $this->fileUploader->storeFile($uploadConfig, $file->getRealPath());
                $uploadResult['transferKey'] = $pathOrUuid;
            }
            if ($widget->hasErrors()) {
                $error = $widget->getErrorAsString();
            }
        } catch (TranslatableExceptionInterface $e) {
            $error = $this->translator->trans($e->getTranslatableText(), $e->getParams(), 'contao_default');
            $this->contaoErrorLogger?->error($e->getMessage());
        } catch (\Throwable $e) {
            if ($this->debug) {
                throw $e;
            }

            $error = $this->translator->trans('ERR.filepond_general_upload_error', [], 'contao_default');
            $this->contaoErrorLogger?->error($e->getMessage());
        } finally {
            unset($_FILES[$widget->name.'_chunk']);
            $request->files->remove($widget->name.'_chunk');

            if (isset($error)) {
                $event->setResponse(
                    new JsonResponse([
                        'success' => false,
                        'filePondItemId' => $event->getFilePondItemId(),
                        'error' => $error,
                    ]),
                );

                return;
            }
        }

        // Everything ok! Send the transfer key base64 encoded to the client.
        $response = [
            'success' => true,
            'isChunk' => true,
            'completed' => true,
            'offset' => $event->getOffset(),
            'totalSize' => $event->getTotalSize(),
            'filePondItemId' => $event->getFilePondItemId(),
            'error' => null,
            'transferKey' => base64_encode($uploadResult['transferKey']),
            'directUpload' => $uploadConfig->isDirectUploadEnabled(),
        ];

        $event->setResponse(new JsonResponse($response, 200));
    }
}
