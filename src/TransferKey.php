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

namespace Markocupic\ContaoFilepondUploader;

use Symfony\Component\DependencyInjection\Attribute\Autowire;

class TransferKey
{
    private const PREFIX = 'filepond';

    public function __construct(
        #[Autowire('%kernel.secret')]
        private readonly string $secret,
    ) {
    }

    public function generate(): string
    {
        $uniqueId = uniqid();
        $hash = $this->getHash($uniqueId);

        return \sprintf('%s_%s_%s', $this->getPrefix(), $uniqueId, $hash);
    }

    public function validate(string $transferKey): bool
    {
        $parts = explode('_', $transferKey);

        if (3 !== \count($parts)) {
            return false;
        }

        if ($parts[0] !== $this->getPrefix()) {
            return false;
        }

        $uniqueId = $parts[1];
        $hash = $parts[2];
        $expected = $this->getHash($uniqueId);

        // Use hash_equals to avoid timing attacks
        return hash_equals($expected, $hash);
    }

    private function getPrefix(): string
    {
        return self::PREFIX;
    }

    private function getHash(string $value): string
    {
        return hash('sha256', $this->secret.$value);
    }
}
