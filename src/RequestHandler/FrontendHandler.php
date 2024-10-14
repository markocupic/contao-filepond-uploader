<?php

declare(strict_types=1);

/*
 * This file is part of Contao Filepond Uploader.
 *
 * (c) Marko Cupic 2024 <m.cupic@gmx.ch>
 * @license GPL-3.0-or-later
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 * @link https://github.com/markocupic/contao-filepond-uploader
 */

namespace Markocupic\ContaoFilepondUploader\RequestHandler;

use Contao\CoreBundle\Monolog\ContaoContext;
use Contao\CoreBundle\Routing\ScopeMatcher;
use Markocupic\ContaoFilepondUploader\Widget\FrontendWidget;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[Autoconfigure(public: true)]
class FrontendHandler
{
    use HandlerTrait;

    /**
     * FrontendHandler constructor.
     */
    public function __construct(
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly LoggerInterface $logger,
        private readonly ScopeMatcher $scopeMatcher,
        #[Autowire('%kernel.project_dir%')]
        private readonly string $projectDir,
    ) {
    }

    /**
     * Handle widget initialization request.
     */
    public function handleWidgetInitRequest(Request $request, FrontendWidget $widget): Response|null
    {
        if (
            !$request->isXmlHttpRequest()
            || empty($request->headers->get('filePondItemId'))
            || $widget->name !== $request->headers->get('name')
            || $request->attributes->get('filepond_ajax')
        ) {
            return null;
        }

        // Add the item id to the request attributes
        $request->attributes->set('filePondItemId', $request->headers->get('filePondItemId'));

        // Avoid circular reference
        $request->attributes->set('filepond_ajax', true);

        try {
            $response = $this->dispatchRequest($request, $widget);
        } catch (\Exception $e) {
            $caller = $e->getTrace()[1];
            $func = $caller['class'].'::'.$caller['function'];

            $this->logger->log(
                LogLevel::ERROR,
                $e->getMessage(),
                ['contao' => new ContaoContext($func, ContaoContext::ERROR)]
            );

            $response = new Response('Bad Request', 400);
        }

        return $response;
    }

    /**
     * Handle upload request.
     *
     * @throw \RuntimeException
     */
    public function handleUploadRequest(Request $request, FrontendWidget $widget): JsonResponse
    {
        $this->validateRequest($request);

        return $this->getUploadResponse($this->eventDispatcher, $request, $widget);
    }

    /**
     * Handle reload request.
     *
     * @throw \RuntimeException
     */
    public function handleReloadRequest(Request $request, FrontendWidget $widget): Response
    {
        $this->validateRequest($request);

        // Set the value from request
        $widget->value = $this->parseValue($request->request->get('value'), $this->projectDir);

        return $this->getReloadResponse($this->eventDispatcher, $request, $widget);
    }

    /**
     * Dispatch the request.
     *
     * @return JsonResponse|null
     */
    private function dispatchRequest(Request $request, FrontendWidget $widget): Response|null
    {
        $response = null;

        // File upload
        if ('filepond_upload' === $request->request->get('action')) {
            $response = $this->handleUploadRequest($request, $widget);
        }

        // Widget reload
        if ('fineuploader_reload' === $request->request->get('action')) {
            //$response = $this->handleReloadRequest($request, $widget);
        }

        return $response;
    }

    /**
     * Validate the request.
     */
    private function validateRequest(Request $request): void
    {
        if (!$this->scopeMatcher->isFrontendRequest($request)) {
            throw new \RuntimeException('This method can be executed only in the frontend scope');
        }
    }
}
