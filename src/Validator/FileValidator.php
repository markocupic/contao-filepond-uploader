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

use Contao\StringUtil;
use Markocupic\ContaoFilepondUploader\Validator\Exception\InvalidFileException;
use Markocupic\ContaoFilepondUploader\Widget\FilepondFrontendWidget;

readonly class FileValidator
{
    public function validateFileChecksum(string $filePath, string $checksumExpected): bool
    {
        $file = new \SplFileInfo($filePath);

        $actualChecksum = hash_file('sha256', $file->getRealPath());

        if ($checksumExpected !== $actualChecksum) {
            throw new InvalidFileException('Checksum mismatch', 'ERR.file.checksummissmatch');
        }

        return true;
    }

    public function validateExtension(string $basename, FilepondFrontendWidget $widget): bool
    {
        $config = $widget->getConfiguration();

        $allowed = StringUtil::trimsplit(',', $config['extensions'] ?? '');
        $allowed = array_unique(array_map('strtolower', $allowed));

        if (empty($allowed)) {
            return true;
        }

        $actual = strtolower(trim((string) pathinfo($basename, PATHINFO_EXTENSION)));

        if ('' === $actual || !\in_array($actual, $allowed, true)) {
            throw new InvalidFileException('File invalid no extension.', 'ERR.extensionsOnly', [implode(', ', $allowed)]);
        }

        return true;
    }

    public function validateMaxFileSize(string $filePath, FilepondFrontendWidget $widget): bool
    {
        $maxAllowed = $widget->getMaximumUploadSize();

        if ($maxAllowed <= 0) {
            return true;
        }

        // Do not use cached file size
        clearstatcache(true, $filePath);

        $size = filesize($filePath);

        if (false === $size) {
            throw new InvalidFileException('Could not determine file size.', 'ERR.filesize', [$maxAllowed]);
        }

        if ($size > $maxAllowed) {
            throw new InvalidFileException(\sprintf('File is too big (%d bytes).', $size), 'ERR.filesize', [$maxAllowed]);
        }

        return true;
    }

    public function validateMinFileSize(string $filePath, FilepondFrontendWidget $widget): bool
    {
        $minAllowed = $widget->getMinimumUploadSize();

        if ($minAllowed <= 0) {
            return true;
        }

        // Do not use cached file size
        clearstatcache(true, $filePath);

        $size = filesize($filePath);

        if (false === $size) {
            throw new InvalidFileException('Could not determine file size.', 'ERR.filesize', [$minAllowed]);
        }

        if ($size < $minAllowed) {
            throw new InvalidFileException(\sprintf('File is too small (%d bytes).', $size), 'ERR.filepond_file_minsize', [$minAllowed]);
        }

        return true;
    }
}
