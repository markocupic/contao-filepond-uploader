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

namespace Markocupic\ContaoFilepondUploader\RequestHandler;

use Contao\Input;
use Contao\StringUtil;
use Contao\Validator;
use Markocupic\ContaoFilepondUploader\Event\FileUploadEvent;
use Markocupic\ContaoFilepondUploader\Widget\BaseWidget;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Filesystem\Path;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
// use Terminal42\FineUploaderBundle\Event\WidgetReloadEvent;
use Symfony\Component\HttpFoundation\Response;

trait HandlerTrait
{
    /**
     * Get the file upload response.
     */
    protected function getUploadResponse(EventDispatcherInterface $eventDispatcher, Request $request, BaseWidget $widget): JsonResponse
    {
        $event = new FileUploadEvent($request, new JsonResponse(), $widget);
        $eventDispatcher->dispatch($event);

        return $event->getResponse();
    }

    /**
     * Get the widget reload response.
     *
     * @return Response
     */
    protected function getReloadResponse(EventDispatcherInterface $eventDispatcher, Request $request, BaseWidget $widget)
    {
        $event = new WidgetReloadEvent($request, new Response(), $widget);
        $eventDispatcher->dispatch($event);

        return $event->getResponse();
    }

    /**
     * Parse the value by converting UUIDs to binary data.
     *
     * @param string $value
     *
     * @return string
     */
    protected function parseValue($value, string $projectDir)
    {
        $value = StringUtil::trimsplit(',', Input::decodeEntities($value));

        foreach ($value as $k => $v) {
            if (Validator::isUuid($v) && !is_file(Path::join($projectDir, $v))) {
                $value[$k] = StringUtil::uuidToBin($v);
            }
        }

        return serialize($value);
    }
}
