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

namespace Markocupic\ContaoFilepondUploader\Upload;

use Contao\Dbafs;
use Contao\FilesModel;
use Contao\StringUtil;
use Contao\Validator;
use Markocupic\ContaoFilepondUploader\Upload\Exception\OverrideFileException;
use Markocupic\ContaoFilepondUploader\Upload\Exception\UndefinedUploadFolderException;
use Markocupic\ContaoFilepondUploader\UploaderConfig;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Path;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;

readonly class FileUploader
{
    public function __construct(
        private LoggerInterface|null $contaoFilesLogger,
        private Filesystem $filesystem,
        #[Autowire('%kernel.project_dir%')]
        private string $projectDir,
        #[Autowire('%markocupic_contao_filepond_uploader.tmp_path%')]
        private string $tmpPath,
    ) {
    }

    /**
     * This will move the file from the tmp folder to system/tmp.
     */
    public function move(UploadedFile $file, string $transferKey): File
    {
        $uploadFolder = $this->getUploadFolder($transferKey);
        $fileName = $this->getSanitizedFilename($file->getClientOriginalName());

        $file = $file->move($uploadFolder, $fileName);

        $this->filesystem->chmod($file->getRealPath(), 0666 & ~umask());

        return $file;
    }

    /**
     * Store a single file to destination folder
     * and return either the relative path or the UUID.
     */
    public function storeFile(UploaderConfig $config, string $tmpFilePath): string
    {
        if (Validator::isUuid($tmpFilePath) && null !== FilesModel::findByUuid($tmpFilePath)) {
            // We return a UUID here, because the file was uploaded directly and added to dbafs.
            return $tmpFilePath;
        }

        $tmpFilePath = Path::makeAbsolute($tmpFilePath, $this->projectDir);

        if (!is_file($tmpFilePath)) {
            throw new \Exception(\sprintf('The file "%s" does not exist', $tmpFilePath));
        }

        if ('' === $config->getUploadFolder()) {
            throw new UndefinedUploadFolderException('Upload stopped! The upload folder is not defined.', 'ERR.filepond_upload_folder_not_defined');
        }

        // Move the temporary file
        if ($config->isStoreFileEnabled() && $config->getUploadFolder()) {
            $targetFolder = Path::makeAbsolute($config->getUploadFolder(), $this->projectDir);

            // The file was directly uploaded and not added to dbafs
            if (str_starts_with($tmpFilePath, $targetFolder)) {
                return Path::makeRelative($tmpFilePath, $this->projectDir);
            }

            $newFilePath = $this->moveTmpFile($tmpFilePath, $targetFolder, $config->isDoNotOverwriteEnabled());
            $relFilePath = Path::makeRelative($newFilePath, $this->projectDir);

            // System log
            $this->contaoFilesLogger?->info('File "'.basename($newFilePath).'" has been uploaded');

            // Add to dbafs
            if ($config->isAddToDbafsEnabled() && Dbafs::shouldBeSynchronized($relFilePath)) {
                $objModel = FilesModel::findByPath($relFilePath);

                if (null === $objModel) {
                    $objModel = Dbafs::addResource($relFilePath);

                    if (null !== $objModel) {
                        $strUuid = StringUtil::binToUuid($objModel->uuid);
                    }
                }

                // Update the hash of the target folder
                $uploadFolder = \dirname($relFilePath);
                Dbafs::updateFolderHashes($uploadFolder);
            }
        }

        return match (true) {
            !empty($strUuid) => $strUuid,
            !empty($relFilePath) => $relFilePath,
            default => Path::makeRelative($tmpFilePath, $this->projectDir),
        };
    }

    private function getSanitizedFilename(string $filename): string
    {
        return StringUtil::sanitizeFileName($filename);
    }

    private function getUploadFolder(string $transferKey): string
    {
        if (Validator::isInsecurePath($this->tmpPath)) {
            throw new \InvalidArgumentException('Invalid target path '.$this->tmpPath);
        }

        $uploadFolder = Path::join($this->projectDir, $this->tmpPath, $transferKey);

        $this->filesystem->mkdir($uploadFolder, 0755);

        return $uploadFolder;
    }

    /**
     * Move the temporary file to its final destination.
     *
     * @param string $tempFilePath absolute path to the temporary file
     * @param string $destination  absolute path to the destination folder
     */
    private function moveTmpFile(string $tempFilePath, string $destination, bool $doNotOverride = false): string
    {
        if (!is_file($tempFilePath)) {
            return '';
        }

        // The file is not temporary
        if (false === stripos($tempFilePath, Path::join($this->projectDir, $this->tmpPath))) {
            return $tempFilePath;
        }

        $new = Path::join($destination, basename($tempFilePath));

        // Do not overwrite existing files
        if ($doNotOverride) {
            $new = Path::join($destination, $this->getUniqueFileName(basename($tempFilePath), $destination));
        }

        $this->filesystem->mkdir(\dirname($new));

        // Delete the file if it already exists and overriding is allowed
        if (!$doNotOverride && is_file($new)) {
            $this->filesystem->remove($new);
        }

        try {
            // Try to rename the file (Will throw an IOException if the file already exists).
            $this->filesystem->rename($tempFilePath, $new);
        } catch (\Exception $e) {
            throw new OverrideFileException('File with same name already exists.', 'ERR.filepond_can_not_override_file_in_destination', [basename($new)]);
        }

        // Set the default CHMOD
        $this->filesystem->chmod($new, 0666 & ~umask());

        // Delete the parent directory too!
        // {project_dir}/system/tmp/filepond_69506bcd96821_3c6dfd158e092ccb9e97c1fec850fb9532609937c6c445a677a2594df71a7722/my_file.jpg
        if (is_dir(\dirname($tempFilePath)) && str_starts_with(basename(\dirname($tempFilePath)), 'filepond_')) {
            $this->filesystem->remove(\dirname($tempFilePath));
        }

        return $new;
    }

    /**
     * This method generates a unique file name for a file to be uploaded.
     * If a file with the same name already exists,
     * it adds a numerical suffix (e.g. file__1.jpg, file__2.jpg)
     * to avoid naming conflicts.
     */
    private function getUniqueFileName(string $basename, string $folder): string
    {
        if (!is_file(Path::join($folder, $basename))) {
            return $basename;
        }

        $offset = 1;
        $pathInfo = pathinfo($basename);
        $name = $pathInfo['filename'];
        $extension = $pathInfo['extension'];

        // Uses the Symfony Finder to find all files in the target folder and convert their relative path names into an array.
        $allFiles = iterator_to_array(Finder::create()->files()->in($folder)->getIterator());
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
        return str_replace($name.'.', $name.'__'.++$offset.'.', $basename);
    }
}
