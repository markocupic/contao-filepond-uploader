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

namespace Markocupic\ContaoFilepondUploader;

use Contao\Config;
use Contao\Message;
use Contao\StringUtil;
use Contao\System;
use Symfony\Component\Filesystem\Path;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class FileUpload extends \Contao\FileUpload
{
    protected string|null $transferKey = null;

    protected bool $doNotOverwrite = false;

    protected array $extensions = [];

    protected int $minFileSize = 0;

    protected int $maxFileSize;

    protected int $imageWidth;

    protected int $imageHeight;

    protected int $gdMaxImgWidth;

    protected int $gdMaxImgHeight;

    /**
     * Temporary store target from uploadTo() to make it available to getFilesFromGlobal().
     */
    private string $target = '';

    public function __construct(string $name, array $arrConfiguration)
    {
        parent::__construct();

        $this->setName($name);

        $this->extensions = StringUtil::trimsplit(',', strtolower($arrConfiguration['extensions'] ?? Config::get('uploadTypes')));
        $this->maxFileSize = (int) $arrConfiguration['maxlength'] ?? 0;
        $this->gdMaxImgWidth = (int) Config::get('gdMaxImgWidth') ?? 0;
        $this->gdMaxImgHeight = (int) Config::get('gdMaxImgHeight') ?? 0;
    }

    public function getName(): string
    {
        return $this->strName;
    }

    public function isDoNotOverwrite(): bool
    {
        return $this->doNotOverwrite;
    }

    public function setDoNotOverwrite(bool $doNotOverwrite): self
    {
        $this->doNotOverwrite = $doNotOverwrite;

        return $this;
    }

    public function getTransferKey(): string
    {
        if (empty($this->transferKey)) {
            $this->transferKey = System::getContainer()->get('markocupic_contao_filepond_uploader.transfer_key')->generate();
        }

        return $this->transferKey;
    }

    public function getExtensions(): array
    {
        return $this->extensions;
    }

    public function setExtensions(array $extensions): self
    {
        $this->extensions = array_map('strtolower', $extensions);

        return $this;
    }

    public function addExtension(string $extension): self
    {
        $this->extensions[] = strtolower($extension);

        return $this;
    }

    public function getMinFileSize(): int
    {
        return $this->minFileSize;
    }

    public function setMinFileSize(int $minFileSize): self
    {
        $this->minFileSize = $minFileSize;

        return $this;
    }

    public function getMaxFileSize(): int
    {
        return $this->maxFileSize;
    }

    public function setMaxFileSize(int $maxFileSize): self
    {
        $this->maxFileSize = $maxFileSize;

        return $this;
    }

    public function getImageWidth(): int
    {
        return $this->imageWidth;
    }

    public function setImageWidth(int $imageWidth): self
    {
        $this->imageWidth = $imageWidth;

        return $this;
    }

    public function getImageHeight(): int
    {
        return $this->imageHeight;
    }

    public function setImageHeight(int $imageHeight): self
    {
        $this->imageHeight = $imageHeight;

        return $this;
    }

    public function getGdMaxImgWidth(): int
    {
        return $this->gdMaxImgWidth;
    }

    public function setGdMaxImgWidth(int $gdMaxImgWidth): self
    {
        $this->gdMaxImgWidth = $gdMaxImgWidth;

        return $this;
    }

    public function getGdMaxImgHeight(): int
    {
        return $this->gdMaxImgHeight;
    }

    public function setGdMaxImgHeight(int $gdMaxImgHeight): self
    {
        $this->gdMaxImgHeight = $gdMaxImgHeight;

        return $this;
    }

    public function uploadTo($strTarget): array
    {
        // Set the temp target folder (system/tmp/...)
        $this->target = $strTarget;

        // Temporary override the configuration
        $uploadTypes = Config::get('uploadTypes');
        Config::set('uploadTypes', implode(',', $this->extensions));

        $maxFileSize = Config::get('maxFileSize');
        Config::set('maxFileSize', $this->maxFileSize);

        try {
            // Perform the file upload
            $result = parent::uploadTo($strTarget);
        } catch (\Exception $e) {
            throw $e;
        } finally {
            // Restore the configuration
            Config::set('uploadTypes', $uploadTypes);
            Config::set('maxFileSize', $maxFileSize);
        }

        return $result;
    }

    /**
     * Get the new file name if it already exists in the folder.
     */
    public static function getFileName(string $uploadedFile, string $uploadFolder): string
    {
        $projectDir = System::getContainer()->getParameter('kernel.project_dir');

        if (!file_exists(Path::join($projectDir, $uploadFolder, $uploadedFile))) {
            return $uploadedFile;
        }

        $offset = 1;
        $pathinfo = pathinfo($uploadedFile);
        $name = $pathinfo['filename'];

        /** @var Finder<SplFileInfo> $files */
        $files = Finder::create()
            ->in($projectDir.'/'.$uploadFolder)
            ->files()
            ->name('/^'.preg_quote($name, '/').'.*\.'.preg_quote($pathinfo['extension'], '/').'/')
        ;

        foreach ($files as $file) {
            $fileName = $file->getFilename();
            dump($fileName);
            if (preg_match('/__[0-9]+\.'.preg_quote($pathinfo['extension'], '/').'$/', $fileName)) {
                $fileName = str_replace('.'.$pathinfo['extension'], '', $fileName);
                $value = (int) substr($fileName, strrpos($fileName, '_') + 1);
                $offset = max($offset, $value);
            }
        }

        return str_replace($name.'.', $name.'__'.++$offset.'.', $uploadedFile);
    }

    protected function getFilesFromGlobal(): array
    {
        if (\is_array($_FILES[$this->strName]['name'] ?? null)) {
            $files = parent::getFilesFromGlobal();
        } else {
            $files = [$_FILES[$this->strName]];
        }

        if ($this->doNotOverwrite) {
            foreach ($files as $k => $file) {
                $files[$k]['name'] = static::getFileName($file['name'], $this->target);
            }
        }

        // Validate the minimum file size and skip from the parent call
        if ($this->minFileSize > 0) {
            $minlength_kb_readable = static::getReadableSize($this->minFileSize);

            foreach ($files as $k => $file) {
                if (!$file['error'] && $file['size'] < $this->minFileSize) {
                    Message::addError(\sprintf($GLOBALS['TL_LANG']['ERR']['minFileSize'], $minlength_kb_readable));
                    $this->blnHasError = true;
                    unset($files[$k]);
                }
            }
        }

        return $files;
    }
}
