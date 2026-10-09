<?php

declare(strict_types=1);

/*
 * This file is part of Contao Filepond Uploader.
 *
 * (c) Marko Cupic <m.cupic@gmx.ch>
 * @license GPL-3.0-or-later
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 * @link https://github.com/markocupic/contao-filepond-uploader
 */

namespace Markocupic\ContaoFilepondUploader\Event;

use Markocupic\ContaoFilepondUploader\Widget\FilepondFrontendWidget;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\EventDispatcher\Event;

class FileUploadEvent extends Event
{
    private readonly string $fileChecksum;

    public function __construct(
        private Request $request,
        private JsonResponse $response,
        private FilepondFrontendWidget $widget,
    ) {
        $this->fileChecksum = $request->request->get('fileChecksum');
    }

    public function getRequest(): Request
    {
        return $this->request;
    }

    public function setRequest(Request $request): void
    {
        $this->request = $request;
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

    public function setWidget(FilepondFrontendWidget $widget): void
    {
        $this->widget = $widget;
    }

    public function getFileChecksum(): string
    {
        return $this->fileChecksum;
    }
}
