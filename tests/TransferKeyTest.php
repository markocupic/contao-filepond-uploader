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

namespace Markocupic\ContaoFilepondUploader\Tests;

use Markocupic\ContaoFilepondUploader\TransferKey;
use PHPUnit\Framework\TestCase;

class TransferKeyTest extends TestCase
{
    public function testGeneratesValidTransferKeys(): void
    {
        $transferKey = new TransferKey('secret');
        $key = $transferKey->generate();

        $this->assertStringStartsWith(TransferKey::PREFIX_FILE_UPLOAD.'_', $key);
        $this->assertTrue($transferKey->validate($key));
        $this->assertNotSame($key, $transferKey->generate());
    }

    public function testRejectsManipulatedTransferKeys(): void
    {
        $transferKey = new TransferKey('secret');
        [$prefix, $uniqueId, $hash] = explode('_', $transferKey->generate());

        $this->assertFalse($transferKey->validate(\sprintf('%s_%s_%s', $prefix, $uniqueId.'x', $hash)));
        $this->assertFalse($transferKey->validate(\sprintf('%s_%s_%s', $prefix, $uniqueId, str_repeat('0', 64))));
        $this->assertFalse($transferKey->validate(\sprintf('other_%s_%s', $uniqueId, $hash)));
        $this->assertFalse($transferKey->validate('../../etc/passwd'));
        $this->assertFalse($transferKey->validate(''));
    }

    public function testRejectsTransferKeysOfAnotherSecret(): void
    {
        $key = (new TransferKey('secret'))->generate();

        $this->assertFalse((new TransferKey('another-secret'))->validate($key));
    }
}
