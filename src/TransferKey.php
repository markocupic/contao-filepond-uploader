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

readonly class TransferKey
{
    public const PREFIX_FILE_UPLOAD = 'filepond';

    private const TRANSFER_KEY_PARTS_COUNT = 3;

    private const PART_INDEX_PREFIX = 0;

    private const PART_INDEX_UNIQUE_ID = 1;

    private const PART_INDEX_HASH = 2;

    public function __construct(
        #[Autowire('%kernel.secret')]
        private string $secret,
    ) {
    }

    public function generate(): string
    {
        $uniqueId = uniqid();
        $hash = $this->generateHash($uniqueId);

        return \sprintf('%s_%s_%s', self::PREFIX_FILE_UPLOAD, $uniqueId, $hash);
    }

    public function validate(string $transferKey): bool
    {
        $parts = $this->parseTransferKey($transferKey);

        if (null === $parts) {
            return false;
        }

        $expected = $this->generateHash($parts['uniqueId']);

        // Use hash_equals to avoid timing attacks
        return hash_equals($expected, $parts['hash']);
    }

    /**
     * Parse transfer key into its components.
     *
     * @return array{uniqueId: string, hash: string}|null
     */
    private function parseTransferKey(string $transferKey): array|null
    {
        $parts = explode('_', $transferKey);

        if (self::TRANSFER_KEY_PARTS_COUNT !== \count($parts)) {
            return null;
        }

        if (self::PREFIX_FILE_UPLOAD !== $parts[self::PART_INDEX_PREFIX]) {
            return null;
        }

        return [
            'uniqueId' => $parts[self::PART_INDEX_UNIQUE_ID],
            'hash' => $parts[self::PART_INDEX_HASH],
        ];
    }

    private function generateHash(string $value): string
    {
        return hash('sha256', $this->secret.$value);
    }
}
