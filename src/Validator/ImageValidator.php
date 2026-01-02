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

namespace Markocupic\ContaoFilepondUploader\Validator;

use Markocupic\ContaoFilepondUploader\Validator\Exception\InvalidImageResolutionException;
use Markocupic\ContaoFilepondUploader\Widget\FilepondFrontendWidget;

readonly class ImageValidator
{
    public function isImage(string $filePath): bool
    {
        $info = @getimagesize($filePath);

        if (false === $info) {
            return false;
        }

        return true;
    }

    public function validateImageResolution(string $filePath, FilepondFrontendWidget $widget): bool
    {
        if (!$this->isImage($filePath)) {
            return false;
        }

        $config = $widget->getUploaderConfig();

        $minWidth = $config->getMinImageWidth();
        $minHeight = $config->getMinImageHeight();
        $maxWidth = $config->getMaxImageWidth();
        $maxHeight = $config->getMaxImageHeight();

        $info = @getimagesize($filePath);
        $basename = basename($filePath);

        [$actualWidth, $actualHeight] = $info;

        // Image width is smaller than the minimum image width
        if ($minWidth > 0 && $actualWidth < $minWidth) {
            throw new InvalidImageResolutionException('Image width is smaller than the minimum image width.', 'ERR.filepond_file_minwidth', [$basename, $minWidth]);
        }

        // Image height is smaller than the minimum image height
        if ($minHeight > 0 && $actualHeight < $minHeight) {
            throw new InvalidImageResolutionException('Image height is smaller than the minimum image height.', 'ERR.filepond_file_minheight', [$basename, $minHeight]);
        }

        // Image exceeds maximum image width
        if ($maxWidth > 0 && $actualWidth > $maxWidth) {
            throw new InvalidImageResolutionException('Image width exceeds the maximum image width.', 'ERR.filewidth', [$basename, $maxWidth]);
        }

        // Image exceeds maximum image height
        if ($maxHeight > 0 && $actualHeight > $maxHeight) {
            throw new InvalidImageResolutionException('Image height exceeds the maximum image height.', 'ERR.fileheight', [$basename, $maxHeight]);
        }

        return true;
    }
}
