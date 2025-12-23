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
use Contao\File;
use Contao\Files;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Filesystem\Path;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class Filesystem
{
    public function __construct(
        #[Autowire('%kernel.project_dir%')]
        private readonly string $projectDir,
        #[Autowire('%markocupic_contao_filepond_uploader.tmp_path%')]
        private readonly string $tmpPath,
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
        return is_file(Path::join($this->projectDir, $filePath));
    }

    /**
     * Return true if the file temporary exists.
     */
    public function tmpFileExists(string $file): bool
    {
        return $this->fileExists($this->tmpPath.'/'.$file);
    }

    /**
     * Merge multiple temporary files into one.
     */
    public function mergeTmpFiles(array $files, string $fileName): File
    {
        $file = new File($this->getTmpPath().'/'.$fileName);

        foreach ($files as $filePath) {
            $file->append(file_get_contents($this->projectDir.'/'.$filePath), '');
            Files::getInstance()->delete($filePath);
        }

        $file->close();

        return $file;
    }

    /**
     * Get the temporary file name.
     */
    public function getTmpFileName(string $file): string
    {
        return $this->getFileName($file, $this->getTmpPath());
    }

    /**
     * Standardize the file name and remove the invalid characters.
     */
    public function standardizeFileName(string $filename): string
    {
        return str_replace([',', '&'], '_', $filename);
    }

    /**
     * Move the temporary file to its destination.
     *
     * @throws \Exception
     */
    public function moveTmpFile(string $file, string $destination, bool $doNotOverwrite = false): string
    {
        if (!$this->fileExists($file)) {
            return '';
        }

        // The file is not temporary
        if (false === stripos($file, $this->tmpPath)) {
            return $file;
        }

        $new = $destination.'/'.basename($file);

        // Do not overwrite existing files
        if ($doNotOverwrite) {
            $new = $destination.'/'.$this->getFileName(basename($file), $destination);
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
     * Get the new file name if it already exists in the folder.
     */
    private function getFileName(string $filePath, string $folder): string
    {
        if (!$this->fileExists($folder.'/'.$filePath)) {
            return $filePath;
        }

        $offset = 1;
        $pathinfo = pathinfo($filePath);
        $name = $pathinfo['filename'];

        $allFiles = iterator_to_array(Finder::create()->files()->in(Path::join($this->projectDir, $folder))->getIterator());
        $allFiles = array_map(static fn (SplFileInfo $fileInfo) => $fileInfo->getRelativePathname(), $allFiles);

        // Find the files with the same extension
        $files = preg_grep(
            '/^'.preg_quote($name, '/').'.*\.'.preg_quote($pathinfo['extension'], '/').'/',
            $allFiles,
        );

        foreach ($files as $file) {
            if (preg_match('/__[0-9]+\.'.preg_quote($pathinfo['extension'], '/').'$/', $file)) {
                $file = str_replace('.'.$pathinfo['extension'], '', $file);
                $value = (int) substr($file, strrpos($file, '_') + 1);
                $offset = max($offset, $value);
            }
        }

        return str_replace($name.'.', $name.'__'.++$offset.'.', $filePath);
    }
}
