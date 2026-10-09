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

namespace Markocupic\ContaoFilepondUploader\Tests\Image;

use Markocupic\ContaoFilepondUploader\Image\SvgSanitizer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

class SvgSanitizerTest extends TestCase
{
    private string $path;

    protected function setUp(): void
    {
        $this->path = tempnam(sys_get_temp_dir(), 'svg');
    }

    protected function tearDown(): void
    {
        (new Filesystem())->remove($this->path);
    }

    public function testRemovesScriptsAndEventHandlers(): void
    {
        file_put_contents($this->path, '<svg xmlns="http://www.w3.org/2000/svg" onload="alert(1)"><script>alert(2)</script><rect width="10" height="10"/></svg>');

        $this->assertTrue((new SvgSanitizer(new Filesystem()))->sanitizeSvg($this->path));

        $svg = (string) file_get_contents($this->path);

        $this->assertStringNotContainsString('script', $svg);
        $this->assertStringNotContainsString('onload', $svg);
        $this->assertStringContainsString('<rect', $svg);
    }

    public function testSanitizesGzippedSvgs(): void
    {
        file_put_contents($this->path, gzencode('<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script><rect width="10" height="10"/></svg>'));

        $this->assertTrue((new SvgSanitizer(new Filesystem()))->sanitizeSvg($this->path));

        $svg = (string) gzdecode((string) file_get_contents($this->path));

        $this->assertStringNotContainsString('script', $svg);
        $this->assertStringContainsString('<rect', $svg);
    }

    public function testReturnsFalseForEmptyFiles(): void
    {
        file_put_contents($this->path, '');

        $this->assertFalse((new SvgSanitizer(new Filesystem()))->sanitizeSvg($this->path));
    }
}
