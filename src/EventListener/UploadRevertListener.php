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

use Markocupic\ContaoFilepondUploader\Event\UploadRevertEvent;
use Markocupic\ContaoFilepondUploader\Exception\FilepondExceptionNormalizer;
use Markocupic\ContaoFilepondUploader\Exception\TranslatableExceptionInterface;
use Markocupic\ContaoFilepondUploader\TransferKey;
use Markocupic\ContaoFilepondUploader\UploadRevert\Exception\UploadRevertException;
use Markocupic\ContaoFilepondUploader\Validator\Exception\InvalidTransferKeyException;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Path;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\Translation\TranslatorInterface;

readonly class UploadRevertListener
{
    public function __construct(
        private FilepondExceptionNormalizer $exceptionNormalizer,
        private Filesystem $filesystem,
        private TransferKey $transferKey,
        private TranslatorInterface $translator,
        #[Autowire('%markocupic_contao_filepond_uploader.tmp_path%')]
        private string $tmpPath,
        #[Autowire('%kernel.project_dir%')]
        private string $projectDir,
        #[Autowire('%kernel.debug%')]
        private bool $debug,
        private LoggerInterface|null $contaoErrorLogger,
    ) {
    }

    #[AsEventListener]
    public function onRevert(UploadRevertEvent $event): void
    {
        $transferKey = base64_decode($event->getTransferKey(), true);

        try {
            if (!$this->transferKey->validate($transferKey)) {
                throw new InvalidTransferKeyException('Invalid transfer key detected.', 'ERR.filepond_invalid_transfer_key');
            }

            $path = Path::join($this->projectDir, $this->tmpPath, $transferKey);

            if (!is_dir($path)) {
                throw new UploadRevertException('No folder matching the key was found.', 'ERR.filepond_uploaded_temp_resource_not_found');
            }

            // Remove the folder
            $this->filesystem->remove($path);
        } catch (\Throwable $e) {
            if ($this->exceptionNormalizer->supports($e) && $e instanceof TranslatableExceptionInterface) {
                $error = $this->exceptionNormalizer->normalize($e)['error'];
            } else {
                if (!$e instanceof TranslatableExceptionInterface && $this->debug) {
                    throw $e;
                }

                $error = $this->translator->trans('ERR.filepond_general_upload_revert_error', [], 'contao_default');
            }
            $this->contaoErrorLogger?->error($e->getMessage());
        } finally {
            if (isset($error)) {
                $event->setResponse(
                    new JsonResponse([
                        'success' => false,
                        'error' => $error,
                    ]),
                );

                return;
            }
        }

        // Everything ok!
        $response = [
            'success' => true,
            'error' => null,
            'transferKey' => base64_encode($transferKey),
        ];

        $event->setResponse(new JsonResponse($response, 200));
    }
}
