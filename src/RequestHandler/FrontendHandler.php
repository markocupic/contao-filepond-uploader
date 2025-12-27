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

namespace Markocupic\ContaoFilepondUploader\RequestHandler;

use Contao\CoreBundle\Routing\ScopeMatcher;
use Markocupic\ContaoFilepondUploader\Event\ChunkUploadEvent;
use Markocupic\ContaoFilepondUploader\Event\FileUploadEvent;
use Markocupic\ContaoFilepondUploader\Widget\FilepondFrontendWidget;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

#[Autoconfigure(public: true)]
readonly class FrontendHandler
{
    private const ACTIONS = [
        'FILEPOND_UPLOAD' => 'filepond_upload',
        'FILEPOND_UPLOAD_CHUNK' => 'filepond_upload_chunk',
    ];

    public function __construct(
        private EventDispatcherInterface $eventDispatcher,
        private LoggerInterface|null $contaoErrorLogger,
        private ScopeMatcher $scopeMatcher,
    ) {
    }

    /**
     * Handle widget initialization request.
     */
    public function handleWidgetInitRequest(Request $request, FilepondFrontendWidget $widget): Response|null
    {
        if ($widget->name !== $request->headers->get('name')) {
            return null;
        }

        if (!$request->isXmlHttpRequest()) {
            return null;
        }

        if ($request->attributes->get('filepond_ajax')) {
            return null;
        }

        try {
            $this->validateRequest($request);

            $action = $this->getAction($request);
            if ($action === self::ACTIONS['FILEPOND_UPLOAD_CHUNK']) {
                // Do some additional validation for chunk requests
                $this->validateChunkRequest($request);
            }
        } catch (\Exception $e) {
            $this->contaoErrorLogger?->error($e->getMessage());

            return new Response('Bad Request', 400);
        }

        // Avoid circular reference
        $request->attributes->set('filepond_ajax', true);

        return $this->getUploadResponse($this->eventDispatcher, $request, $widget);
    }

    /**
     * Get the file upload response.
     */
    protected function getUploadResponse(EventDispatcherInterface $eventDispatcher, Request $request, FilepondFrontendWidget $widget): JsonResponse
    {
        $action = $this->getAction($request);

        if ($action === self::ACTIONS['FILEPOND_UPLOAD_CHUNK']) {
            $event = new ChunkUploadEvent($request, new JsonResponse(), $widget);
            $eventDispatcher->dispatch($event);

            return $event->getResponse();
        }

        if ($action === self::ACTIONS['FILEPOND_UPLOAD']) {
            $event = new FileUploadEvent($request, new JsonResponse(), $widget);
            $eventDispatcher->dispatch($event);

            return $event->getResponse();
        }

        throw new \RuntimeException('Invalid action submitted!');
    }

    /**
     * Validate the request.
     */
    private function validateRequest(Request $request): void
    {
        if (!$this->scopeMatcher->isFrontendRequest($request)) {
            throw new \RuntimeException('This method can be executed only in the frontend scope');
        }

        if (!$request->headers->has('filePondItemId')) {
            throw new BadRequestHttpException('Required header "filePondItemId" is missing.');
        }

        if (!$request->isMethod(Request::METHOD_POST)) {
            throw new BadRequestHttpException('Request method must POST.');
        }

        $action = $this->getAction($request);

        if (!\in_array($action, self::ACTIONS, true)) {
            throw new BadRequestHttpException('Invalid $_POST["action"] value submitted!');
        }
    }

    /**
     * Validate the request.
     */
    private function validateChunkRequest(Request $request): void
    {
        $action = $this->getAction($request);

        if (self::ACTIONS['FILEPOND_UPLOAD_CHUNK'] !== $action) {
            throw new BadRequestHttpException('Invalid $_POST["action"] value submitted!');
        }

        $post = ['fileName', 'offset', 'totalSize'];

        foreach ($post as $key) {
            if (!$request->request->has($key)) {
                throw new BadRequestHttpException('Missing POST parameter: '.$key);
            }
        }

        if (empty($_FILES['chunk'])) {
            throw new BadRequestHttpException('Missing FILES parameter: "chunk"');
        }
    }

    private function getAction(Request $request): string
    {
        return $request->request->get('action');
    }
}
