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
use Contao\Input;
use Contao\StringUtil;
use Contao\Validator;
use Markocupic\ContaoFilepondUploader\Event\FileUploadEvent;
use Markocupic\ContaoFilepondUploader\Widget\FilepondFrontendWidget;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Filesystem\Path;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

#[Autoconfigure(public: true)]
readonly class FrontendHandler
{
    public function __construct(
        private EventDispatcherInterface $eventDispatcher,
        private LoggerInterface $contaoErrorLogger,
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
        } catch (\Exception $e) {
            $this->contaoErrorLogger->error($e->getMessage());

            return new Response('Bad Request', 400);
        }

        // Add the item id to the request attributes
        $request->attributes->set('filePondItemId', $request->headers->get('Fileponditemid'));

        // Avoid circular reference
        $request->attributes->set('filepond_ajax', true);

        try {
            $response = $this->getUploadResponse($this->eventDispatcher, $request, $widget);
        } catch (\Exception $e) {
            $this->contaoErrorLogger->error($e->getMessage());

            $response = new Response('Bad Request', 400);
        }

        return $response;
    }

    /**
     * Get the file upload response.
     */
    protected function getUploadResponse(EventDispatcherInterface $eventDispatcher, Request $request, FilepondFrontendWidget $widget): JsonResponse
    {
        $event = new FileUploadEvent($request, new JsonResponse(), $widget);
        $eventDispatcher->dispatch($event);

        return $event->getResponse();
    }

    /**
     * Validate the request.
     */
    private function validateRequest(Request $request): void
    {
        if (!$this->scopeMatcher->isFrontendRequest($request)) {
            throw new \RuntimeException('This method can be executed only in the frontend scope');
        }

        if (!$request->headers->has('Fileponditemid')) {
            throw new BadRequestHttpException('Required header "Fileponditemid" is missing.');
        }

        if (!$request->isMethod(Request::METHOD_POST)) {
            throw new BadRequestHttpException('Request method must POST.');
        }

        if ('filepond_upload' !== $request->request->get('action')) {
            throw new BadRequestHttpException('Invalid $_POST["action"] value submitted!');
        }
    }
}
