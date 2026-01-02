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

use Markocupic\ContaoFilepondUploader\Event\FileUploadEvent;
use Markocupic\ContaoFilepondUploader\Image\ImageResizer;
use Markocupic\ContaoFilepondUploader\Image\SvgSanitizer;
use Markocupic\ContaoFilepondUploader\TransferKey;
use Markocupic\ContaoFilepondUploader\Upload\FileUploader;
use Markocupic\ContaoFilepondUploader\Validator\Exception\NoFileUploadedException;
use Markocupic\ContaoFilepondUploader\Validator\Exception\TranslatableExceptionInterface;
use Markocupic\ContaoFilepondUploader\Validator\FileValidator;
use Markocupic\ContaoFilepondUploader\Validator\ImageValidator;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\Translation\TranslatorInterface;

readonly class FileUploadListener
{
    public function __construct(
        private FileUploader $fileUploader,
        private FileValidator $fileValidator,
        private ImageResizer $imageResizer,
        private ImageValidator $imageValidator,
        private SvgSanitizer $svgSanitizer,
        private TransferKey $transferKey,
        private TranslatorInterface $translator,
        #[Autowire('%kernel.debug%')]
        private bool $debug,
        private LoggerInterface|null $contaoErrorLogger,
    ) {
    }

    #[AsEventListener]
    public function onFileUpload(FileUploadEvent $event): void
    {
        $widget = $event->getWidget();
        $request = $event->getRequest();
        $files = $request->files->get($widget->name);
        $uploadConfig = $widget->getUploaderConfig();

        $file = match (true) {
            !empty($files) && $files instanceof UploadedFile => $files,
            !empty($files[0]) && $files[0] instanceof UploadedFile => $files[0],
            default => null,
        };

        try {
            if (null === $file) {
                throw new NoFileUploadedException('No file uploaded.', 'ERR.filepond_nofileuploaded');
            }

            $this->fileValidator->validateFileChecksum($file->getRealPath(), $event->getFileChecksum());

            $this->fileValidator->validateExtension($file->getClientOriginalName(), $widget);

            $this->fileValidator->validateMinFileSize($file->getRealPath(), $widget);

            $this->fileValidator->validateMaxFileSize($file->getRealPath(), $widget);

            $extension = strtolower($file->getClientOriginalExtension());

            // Check if the uploaded svg-file contains malicious code
            if (\in_array(strtolower($extension), ['svg', 'svgz'], true) && !$this->svgSanitizer->sanitizeSvg($file->getRealPath())) {
                throw new NoFileUploadedException('The uploaded file is not a valid SVG.', 'ERR.fileerror');
            }

            // Move the file to the upload folder
            $transferKey = $this->transferKey->generate();
            $uploadedFile = $this->fileUploader->move($file, $transferKey);
            $uploadResult = [
                'filePath' => $uploadedFile->getRealPath(),
                'transferKey' => $transferKey,
                'file' => $uploadedFile,
                'directUpload' => false,
            ];

            if (empty($uploadResult) || empty($uploadResult['transferKey']) || empty($uploadResult['filePath'])) {
                throw new NoFileUploadedException('No file uploaded.', 'ERR.filepond_general_upload_error');
            }

            $file = new File($uploadResult['filePath']);

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
                $uploadResult['filePath'] = $pathOrUuid;
                $uploadResult['directUpload'] = true;
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
            unset($_FILES[$widget->name]);
            $request->files->remove($widget->name);

            if (isset($error)) {
                $event->setResponse(
                    new JsonResponse([
                        'success' => false,
                        'filePondItemId' => $event->getRequest()->attributes->get('filePondItemId'),
                        'error' => $error,
                    ]),
                );

                return;
            }
        }

        // Everything ok! Send the transfer key base64 encoded to the client.
        /** @noinspection PhpUndefinedVariableInspection */
        $response = [
            'success' => true,
            'filePondItemId' => $event->getRequest()->attributes->get('filePondItemId'),
            'error' => null,
            'transferKey' => base64_encode($uploadResult['transferKey']),
            'directUpload' => (bool) ($uploadResult['directUpload'] ?? false),
        ];

        $event->setResponse(new JsonResponse($response, 200));
    }
}
