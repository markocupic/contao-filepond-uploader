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

namespace Markocupic\ContaoFilepondUploader\Event;

use Markocupic\ContaoFilepondUploader\Widget\FilepondFrontendWidget;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\EventDispatcher\Event;

class ChunkUploadEvent extends Event
{
    private string $fileName;

    private string $filePondItemId;

    private int $offset;

    private int $totalSize;

    private string $fileChecksum;

    public function __construct(
        private readonly UploadedFile $chunkFile,
        private readonly FilepondFrontendWidget $widget,
        private readonly Request $request,
        private JsonResponse $response,
    ) {
        $this->fileName = $request->request->get('fileName');
        $this->filePondItemId = $request->headers->get('filePondItemId');
        $this->offset = (int) $request->request->get('offset');
        $this->totalSize = (int) $request->request->get('totalSize');
        $this->fileChecksum = $request->request->get('fileChecksum');
    }

    public function getRequest(): Request
    {
        return $this->request;
    }

    public function getResponse(): JsonResponse
    {
        return $this->response;
    }

    public function setResponse(JsonResponse $response): void
    {
        $this->response = $response;
    }

    public function getWidget(): FilepondFrontendWidget
    {
        return $this->widget;
    }

    public function getChunkFile(): UploadedFile
    {
        return $this->chunkFile;
    }

    public function getFileName(): string
    {
        return $this->fileName;
    }

    public function getFilePondItemId(): string
    {
        return $this->filePondItemId;
    }

    public function getOffset(): int
    {
        return $this->offset;
    }

    public function getTotalSize(): int
    {
        return $this->totalSize;
    }

    public function getFileChecksum(): string
    {
        return $this->fileChecksum;
    }
}
