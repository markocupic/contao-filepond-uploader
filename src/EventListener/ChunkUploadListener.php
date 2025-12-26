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

use Contao\StringUtil;
use Contao\Validator;
use Markocupic\ContaoFilepondUploader\Chunk\ChunkProcessor;
use Markocupic\ContaoFilepondUploader\Event\ChunkUploadEvent;
use Markocupic\ContaoFilepondUploader\Uploader;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\JsonResponse;

readonly class ChunkUploadListener
{
    public function __construct(
        private ChunkProcessor $chunkProcessor,
        private Uploader $uploader,
    ) {
    }

    #[AsEventListener]
    public function onChunkUpload(ChunkUploadEvent $event): void
    {
        $this->chunkProcessor->cleanupOldChunks();
        $arrUploadResult = $this->chunkProcessor->processChunk($event->getWidget(), $event->getChunk(), $event->getFileName(), $event->getFilePondItemId(), $event->getOffset(), $event->getTotalSize());

        $config = $event->getWidget()->getUploaderConfig();

        if ($arrUploadResult['completed'] && $arrUploadResult['success']) {
            if ($config->isDirectUploadEnabled()) {
                $newPath = $this->uploader->storeFile($config, $arrUploadResult['filePath']);
                $arrUploadResult['filePath'] = Validator::isBinaryUuid($newPath) ? StringUtil::binToUuid($newPath) : $newPath;
                $arrUploadResult['directUpload'] = true;
            }
        }

        $event->setResponse(new JsonResponse($arrUploadResult));
    }
}
