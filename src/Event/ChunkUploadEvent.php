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
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\EventDispatcher\Event;

class ChunkUploadEvent extends Event
{
    private array $chunk;

    private string $fileName;

    private string $filePondItemId;

    private int $offset;

    private int $totalSize;

    public function __construct(
        private Request $request,
        private Response $response,
        private FilepondFrontendWidget $widget,
    ) {
        $this->chunk = $_FILES['chunk'];
        $this->fileName = $request->request->get('fileName');
        $this->filePondItemId = $request->headers->get('filePondItemId');
        $this->offset = (int) $request->request->get('offset');
        $this->totalSize = (int) $request->request->get('totalSize');
    }

    public function getRequest(): Request
    {
        return $this->request;
    }

    public function getResponse(): Response
    {
        return $this->response;
    }

    public function setResponse(Response $response): void
    {
        $this->response = $response;
    }

    public function getWidget(): FilepondFrontendWidget
    {
        return $this->widget;
    }

    public function getChunk(): array
    {
        return $this->chunk;
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
}
