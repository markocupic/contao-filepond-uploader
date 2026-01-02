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

namespace Markocupic\ContaoFilepondUploader\Image;

use enshrined\svgSanitize\Sanitizer;
use Symfony\Component\Filesystem\Filesystem;

readonly class SvgSanitizer
{
    public function __construct(
        private Filesystem $filesystem,
    ) {
    }

    /**
     * Sanitize an uploaded SVG image, return false if the file cannot be processed.
     */
    public function sanitizeSvg($path): bool
    {
        $strData = @file_get_contents($path);

        if (!$strData) {
            return false;
        }

        $blnGzip = false;

        if (0 === strncmp($strData, hex2bin('1F8B'), 2)) {
            $strData = gzdecode($strData);
            $blnGzip = true;
        }

        if (!$strData) {
            return false;
        }

        $sanitizer = $this->getSanitizer();
        $strData = $sanitizer->sanitize($strData);

        if (!$strData) {
            return false;
        }

        if ($blnGzip) {
            $strData = gzencode($strData);
        }

        $this->filesystem->dumpFile($path, $strData);

        return true;
    }

    private function getSanitizer(): Sanitizer
    {
        return new Sanitizer();
    }
}
