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

namespace Markocupic\ContaoFilepondUploader\Chunk;

use Contao\StringUtil;
use Contao\System;
use Markocupic\ContaoFilepondUploader\TransferKey;
use Markocupic\ContaoFilepondUploader\Widget\FilepondFrontendWidget;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Path;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class ChunkProcessor
{
    public const PREFIX_CHUNK_UPLOAD = 'filepond_chunks';

    public function __construct(
        private readonly Filesystem $filesystem,
        private readonly LoggerInterface $contaoErrorLogger,
        private readonly LoggerInterface $contaoGeneralLogger,
        private readonly TransferKey $transferKey,
        #[Autowire('%kernel.project_dir%')]
        private readonly string $projectDir,
        #[Autowire('%markocupic_contao_filepond_uploader.tmp_path%')]
        private string $tempDir,
    ) {
        $this->tempDir = Path::join($this->projectDir, $this->tempDir);

        // Create the temporary directory if it doesn't exist
        if (!is_dir($this->tempDir)) {
            $this->filesystem->mkdir($this->tempDir, 0755);
        }
    }

    /**
     * Processes an upload chunk.
     */
    public function processChunk(FilepondFrontendWidget $widget, array $chunkData, string $fileName, string $filePondItemId, int $offset, int $totalSize): array
    {
        // Validation
        if (empty($fileName)) {
            throw new \InvalidArgumentException('File name must not be empty');
        }

        if ($offset < 0 || $totalSize <= 0) {
            throw new \InvalidArgumentException('Invalid offset or size values');
        }

        if ($offset >= $totalSize) {
            throw new \InvalidArgumentException('Offset exceeds file size');
        }

        $safeFileName = StringUtil::sanitizeFileName($fileName);

        // Save the chunk file
        $chunkFile = $this->getChunkFilePath($filePondItemId, $offset);

        // Create the chunk directory
        if (!is_dir(\dirname($chunkFile))) {
            $this->filesystem->mkdir(\dirname($chunkFile), 0755);
        }

        // Extract actual chunk data from array
        $chunkContent = $this->extractChunkContent($chunkData);

        if (false === file_put_contents($chunkFile, $chunkContent, LOCK_EX)) {
            throw new FileException('Error saving chunk');
        }

        // Check if all chunks have been received
        $isComplete = $this->isChunkUploadCompleted($filePondItemId, $totalSize);

        if ($isComplete) {
            $transferKey = $this->transferKey->generate();
            $success = false;
            $error = null;

            try {
                // Assemble all chunks
                $finalFile = $this->assembleChunks($transferKey, $filePondItemId, $safeFileName, $totalSize);

                // Validate the file
                $this->validateFile($finalFile, $widget);

                $success = true;
            } catch (\Exception $e) {
                $error = $widget->hasErrors() ? $widget->getErrorAsString() : 'Unknown error';
                $this->contaoErrorLogger->error($e->getMessage());
            }

            return [
                'success' => $success,
                'offset' => $offset,
                'totalSize' => $totalSize,
                'fileName' => $fileName,
                'filePondItemId' => $filePondItemId,
                'transferKey' => $transferKey,
                'filePath' => !empty($finalFile) ? $finalFile->getRealPath() : null,
                'completed' => true,
                'error' => $error,
            ];
        }

        $this->contaoGeneralLogger->debug(\sprintf('File %s has been uploaded by FilePond to "%s" using the Chunk-Method', $fileName, $this->tempDir));

        return [
            'success' => true,
            'offset' => $offset,
            'totalSize' => $totalSize,
            'fileName' => $fileName,
            'filePondItemId' => $filePondItemId,
            'completed' => false,
        ];
    }

    /**
     * Deletes old chunk directories (cleanup function).
     *
     * @param int $maxAge Maximum age in seconds (default: 24 hours)
     */
    public function cleanupOldChunks(int $maxAge = 86400): int
    {
        $cleaned = 0;
        $dirs = glob($this->tempDir.'/chunks_*');

        if (false === $dirs) {
            return 0;
        }

        $now = time();

        foreach ($dirs as $dir) {
            if (is_dir($dir)) {
                $mtime = filemtime($dir);

                if ($now - $mtime > $maxAge) {
                    $this->filesystem->remove($dir);
                    ++$cleaned;
                }
            }
        }

        return $cleaned;
    }

    /**
     * Returns the temporary directory.
     */
    public function getTempDir(): string
    {
        return $this->tempDir;
    }

    /**
     * Extracts the actual chunk content from the uploaded data array.
     */
    private function extractChunkContent(array $chunkData): string
    {
        // If it's a $_FILES array structure
        if (isset($chunkData['tmp_name']) && is_uploaded_file($chunkData['tmp_name'])) {
            $content = file_get_contents($chunkData['tmp_name']);
            if (false === $content) {
                throw new FileException('Failed to read uploaded chunk file');
            }

            return $content;
        }

        // If it's a direct data array with content
        if (isset($chunkData['content'])) {
            return $chunkData['content'];
        }

        // If it's just the raw content as string in an array
        if (isset($chunkData[0]) && \is_string($chunkData[0])) {
            return $chunkData[0];
        }

        throw new \InvalidArgumentException('Invalid chunk data format. Expected file upload array or content string');
    }

    /**
     * Returns the chunk directory for a transfer key.
     */
    private function getChunkDirectory(string $filePondItemId): string
    {
        return $this->tempDir.\sprintf('/%s_%s', self::PREFIX_CHUNK_UPLOAD, $filePondItemId);
    }

    /**
     * Returns the path for a chunk file.
     */
    private function getChunkFilePath(string $filePondItemId, int $offset): string
    {
        return $this->getChunkDirectory($filePondItemId).'/chunk_'.$offset;
    }

    /**
     * Checks if all chunks have been received.
     */
    private function isChunkUploadCompleted(string $filePondItemId, int $totalSize): bool
    {
        $chunkDir = $this->getChunkDirectory($filePondItemId);

        if (!is_dir($chunkDir)) {
            return false;
        }

        // Collect all chunk files
        $chunks = glob($chunkDir.'/chunk_*');

        if (empty($chunks)) {
            return false;
        }

        // Calculate the total size of all chunks
        $receivedSize = 0;

        foreach ($chunks as $chunkFile) {
            $receivedSize += filesize($chunkFile);
        }

        return $receivedSize >= $totalSize;
    }

    /**
     * Assembles all chunks into a final file.
     */
    private function assembleChunks(string $transferKey, string $filePondItemId, string $fileName, int $totalSize): \SplFileInfo
    {
        $chunkDir = $this->getChunkDirectory($filePondItemId);
        $finalFile = Path::join($this->tempDir, $transferKey, $fileName);

        // Create the final file directory
        $this->filesystem->mkdir(\dirname($finalFile));

        // Open the final file for writing
        $handle = fopen($finalFile, 'w');

        if (false === $handle) {
            throw new FileException('Could not create final file');
        }

        try {
            // Collect and sort chunks by offset
            $chunks = glob($chunkDir.'/chunk_*');

            usort(
                $chunks,
                static function ($a, $b) {
                    $offsetA = (int) substr(basename($a), 6);
                    $offsetB = (int) substr(basename($b), 6);

                    return $offsetA <=> $offsetB;
                },
            );

            // Assemble chunks
            foreach ($chunks as $chunkFile) {
                $chunkData = file_get_contents($chunkFile);

                if (false === $chunkData) {
                    throw new FileException('Error reading chunk: '.$chunkFile);
                }

                if (false === fwrite($handle, $chunkData)) {
                    throw new FileException('Error writing to final file');
                }
            }

            // Delete chunk directory
            $this->filesystem->remove($chunkDir);

            fclose($handle);

            // Validate final file size
            $actualSize = filesize($finalFile);

            if ($actualSize !== $totalSize) {
                throw new FileException(\sprintf('File size does not match. Expected: %d, Received: %d', $totalSize, $actualSize));
            }

            return new \SplFileInfo($finalFile);
        } catch (\Exception $e) {
            fclose($handle);

            if (file_exists($finalFile)) {
                unlink($finalFile);
            }

            throw $e;
        }
    }

    private function validateFile(\SplFileInfo $file, FilepondFrontendWidget $widget): bool
    {
        $config = $widget->getUploaderConfig();
        $minSizeLimit = $config->getMinSizeLimit();

        // Validate the minimum size limit
        if ($minSizeLimit > 0 && $file->getSize() < $minSizeLimit) {
            $widget->addError(\sprintf($GLOBALS['TL_LANG']['ERR']['minFileSize'], System::getReadableSize($minSizeLimit)));

            throw new \Exception('The uploaded file is smaller than the minimum allowed size.');
        }

        $maxSizeLimit = $config->getMaxSizeLimit();

        // Validate the maximum size limit
        if ($maxSizeLimit > 0 && $file->getSize() > $maxSizeLimit) {
            $widget->addError(\sprintf($GLOBALS['TL_LANG']['ERR']['filesize'], System::getReadableSize($maxSizeLimit)));

            throw new \Exception('The uploaded file exceeds the maximum allowed size.');
        }

        return true;
    }
}
