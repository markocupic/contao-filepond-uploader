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

use Contao\Config;
use Contao\Files;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Filesystem\Path;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

readonly class Filesystem
{
    public function __construct(
        #[Autowire('%kernel.project_dir%')]
        private string $projectDir,
        #[Autowire('%markocupic_contao_filepond_uploader.tmp_path%')]
        private string $tmpPath,
    ) {
    }

    /**
     * Get the temporary file upload path.
     */
    public function getTmpPath(): string
    {
        return $this->tmpPath;
    }

    /**
     * Return true if the file exists.
     */
    public function fileExists(string $filePath): bool
    {
        $fs = new \Symfony\Component\Filesystem\Filesystem();

        return $fs->exists(Path::join($this->projectDir, $filePath));
    }

    /**
     * Return true if the file temporary exists.
     */
    public function tmpFileExists(string $file): bool
    {
        return $this->fileExists(Path::join($this->tmpPath.'/'.$file));
    }

    /**
     * Get the temporary file name.
     */
    public function getUniqueTmpFileName(string $file): string
    {
        return $this->getFileName($file, $this->getTmpPath());
    }

    /**
     * Standardize the file name and remove the invalid characters.
     */
    public function standardizeFileName(string $filename): string
    {
        // Trim whitespace
        $filename = trim($filename);

        // Windows-forbidden characters
        $forbidden = [
            ',',
            '&',
            '\\', // Backslash
            '/', // Slash
            ':', // Colon
            '*', // Asterisk
            '?', // Question mark
            '"', // Double quote
            '<', // Less-than
            '>', // Greater-than
            '|', // Pipe
        ];

        // Replace forbidden characters with underscore
        $filename = str_replace($forbidden, '_', $filename);

        // Remove ASCII control characters (0–31)
        $filename = preg_replace('/[\x00-\x1F]/', '', $filename);

        // Normalize whitespace
        $filename = preg_replace('/\s+/', ' ', $filename);

        // Prevent empty filenames
        if ('' === $filename) {
            $filename = uniqid('file_');
        }

        return $filename;
    }

    /**
     * Move the temporary file to its destination.
     *
     * @throws \Exception
     */
    public function moveTmpFile(string $file, string $destination, bool $doNotOverride = false): string
    {
        if (!$this->fileExists($file)) {
            return '';
        }

        // The file is not temporary
        if (false === stripos($file, $this->tmpPath)) {
            return $file;
        }

        $new = Path::join($destination, basename($file));

        // Do not overwrite existing files
        if ($doNotOverride) {
            $new = Path::join($destination, $this->getFileName(basename($file), $destination));
        }

        $files = Files::getInstance();
        $files->mkdir(\dirname($new));

        // Try to rename the file
        if (!$files->rename($file, $new)) {
            throw new \Exception(\sprintf('The file "%s" could not be renamed to "%s"', $file, $new));
        }

        // Set the default CHMOD
        $files->chmod($new, Config::get('defaultFileChmod'));

        // Delete the enclosing directory too! -> filepond_323232
        // TL_ROOT/temp_upload_path/filepond_323232/my_file.jpg
        if (str_contains(\dirname($file), 'filepond_')) {
            $fs = new \Symfony\Component\Filesystem\Filesystem();
            $absDirPath = Path::join($this->projectDir, \dirname($file));
            $absTempDirPath = Path::join($this->projectDir, $this->tmpPath);

            if (is_dir($absDirPath)) {
                if ($absDirPath !== $absTempDirPath) {
                    if (str_starts_with($absDirPath, $absTempDirPath)) {
                        $fs->remove($absDirPath);
                    }
                }
            }
        }

        return $new;
    }

    /**
     * This method generates a unique file name for a file to be uploaded.
     * If a file with the same name already exists,
     * it adds a numerical suffix (e.g. file__1.jpg, file__2.jpg)
     * to avoid naming conflicts.
     */
    private function getFileName(string $filePath, string $folder): string
    {
        if (!$this->fileExists(Path::join($folder, $filePath))) {
            return $filePath;
        }

        $offset = 1;
        $pathInfo = pathinfo($filePath);
        $name = $pathInfo['filename'];
        $extension = $pathInfo['extension'];

        // Uses the Symfony Finder to find all files in the target folder and convert their relative path names into an array.
        $allFiles = iterator_to_array(Finder::create()->files()->in(Path::join($this->projectDir, $folder))->getIterator());
        $allFiles = array_map(static fn (SplFileInfo $fileInfo) => $fileInfo->getRelativePathname(), $allFiles);

        // Find the files with the same extension:
        // Searches for all files that:
        // - start with the same base name ($name)
        // - have the same file extension
        // Example: For photo.jpg, the following files are found:
        // photo.jpg, photo__1.jpg, photo__2.jpg, photo_backup.jpg, etc.
        $files = preg_grep(
            '/^'.preg_quote($name, '/').'.*\.'.preg_quote($extension, '/').'/',
            $allFiles,
        );

        // - Checks each file for the pattern __[number].[extension] (e.g. __5.jpg)
        // - Extracts the number after the last underscore
        // - Saves the highest number found in $offset
        // Example: If photo__1.jpg, photo__3.jpg, photo__7.jpg exist, $offset becomes 7.
        foreach ($files as $file) {
            if (preg_match('/__[0-9]+\.'.preg_quote($extension, '/').'$/', $file)) {
                $file = str_replace('.'.$extension, '', $file);
                $value = (int) substr($file, strrpos($file, '_') + 1);
                $offset = max($offset, $value);
            }
        }

        // Increments $offset (e.g. from 7 to 8)
        // Replaces filename with filename__8.
        // Returns the new unique file name
        return str_replace($name.'.', $name.'__'.++$offset.'.', $filePath);
    }
}
