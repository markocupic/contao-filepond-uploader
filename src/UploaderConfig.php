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

class UploaderConfig
{
    /**
     * Allowed extensions.
     */
    private string $extensions = '';

    /**
     * Add the multiple attribute to the file input widget.
     */
    private bool $multiple = false;

    /**
     * Limit.
     */
    private int $limit = 0;

    /**
     * Minimum file size.
     */
    private int $minFileSizeLimit = 0;

    /**
     * Maximum file size.
     */
    private int $maxFileSizeLimit = 0;

    /**
     * Minimum image width.
     */
    private int $minImageWidth = 0;

    /**
     * Maximum image width.
     */
    private int $maxImageWidth = 0;

    /**
     * Minimum image height.
     */
    private int $minImageHeight = 0;

    /**
     * Maximum image height.
     */
    private int $maxImageHeight = 0;

    /**
     * Maximum number of connections.
     */
    private int $parallelUploads = 3;

    /**
     * Allow chunkUploads.
     */
    private bool $chunkUploads = false;

    /**
     * Chunk size.
     */
    private int $chunkSize = 1000000;

    /**
     * Allow direct upload.
     */
    private bool $directUpload = false;

    /**
     * Store file.
     */
    private bool $storeFile = false;

    /**
     * Do not overwrite file.
     */
    private bool $doNotOverwrite = false;

    /**
     * Add to database file system.
     */
    private bool $addToDbafs = false;

    /**
     * Upload folder.
     */
    private string $uploadFolder = 'system/tmp';

    /**
     * Labels.
     */
    private array $labels = [];

    /**
     * Allow image resizing.
     */
    private bool $imgResize = false;

    /**
     * Image resize target width (pixels).
     */
    private int $imgResizeWidth = 0;

    /**
     * Image resize target height (pixels).
     */
    private int $imgResizeHeight = 0;

    /**
     * Allow client side image resizing.
     */
    private bool $imgResizeBrowser = false;

    /**
     * Client side image resize mode.
     */
    private string $imgResizeModeBrowser = 'contain';

    /**
     * Client side image resize upscale.
     */
    private bool $imgResizeUpscaleBrowser = false;

    /**
     * Get the allowed extensions.
     */
    public function getExtensions(): string
    {
        return $this->extensions;
    }

    /**
     * Set the allowed extensions.
     */
    public function setExtensions(string $extensions = ''): self
    {
        $this->extensions = implode(',', array_map(static fn ($el) => '.'.$el, explode(',', $extensions)));

        return $this;
    }

    /**
     * Return true if the multiple attribute should be set to the file input widget.
     */
    public function isMultiple(): bool
    {
        return $this->multiple;
    }

    /**
     * Add the multiple attribute to the file input widget.
     */
    public function enableMultiple(): self
    {
        $this->multiple = true;

        return $this;
    }

    /**
     * Do not add the multiple attribute to the file input widget.
     */
    public function disableMultiple(): self
    {
        $this->multiple = false;

        return $this;
    }

    /**
     * Get the file limit.
     */
    public function getFileLimit(): int
    {
        return $this->limit;
    }

    /**
     * Set the file limit.
     */
    public function setFileLimit(int $limit): self
    {
        $this->limit = $limit;

        return $this;
    }

    /**
     * Get the minimum file size limit.
     */
    public function getMinFileSizeLimit(): int
    {
        return $this->minFileSizeLimit;
    }

    /**
     * Set the minimum file size limit.
     */
    public function setMinFileSizeLimit($minFileSizeLimit): self
    {
        $this->minFileSizeLimit = (int) $minFileSizeLimit;

        return $this;
    }

    /**
     * Get the maximum file size limit.
     */
    public function getMaxFileSizeLimit(): int
    {
        return $this->maxFileSizeLimit;
    }

    /**
     * Set the maximum file size limit.
     */
    public function setMaxFileSizeLimit(int $maxFileSizeLimit): self
    {
        $this->maxFileSizeLimit = $maxFileSizeLimit;

        return $this;
    }

    /**
     * Get the minimum image width.
     */
    public function getMinImageWidth(): int
    {
        return $this->minImageWidth;
    }

    /**
     * Set the minimum image width.
     */
    public function setMinImageWidth(int $minImageWidth): self
    {
        $this->minImageWidth = $minImageWidth;

        return $this;
    }

    /**
     * Get the maximum image width.
     */
    public function getMaxImageWidth(): int
    {
        return $this->maxImageWidth;
    }

    /**
     * Set the maximum image width.
     */
    public function setMaxImageWidth($maxImageWidth): self
    {
        $this->maxImageWidth = (int) $maxImageWidth;

        return $this;
    }

    /**
     * Get the minimum image height.
     */
    public function getMinImageHeight(): int
    {
        return $this->minImageHeight;
    }

    /**
     * Set the minimum image height.
     */
    public function setMinImageHeight(int $minImageHeight): self
    {
        $this->minImageHeight = $minImageHeight;

        return $this;
    }

    /**
     * Get the maximum image height.
     */
    public function getMaxImageHeight(): int
    {
        return $this->maxImageHeight;
    }

    /**
     * Set the maximum image height.
     */
    public function setMaxImageHeight(int $maxImageHeight): self
    {
        $this->maxImageHeight = $maxImageHeight;

        return $this;
    }

    /**
     * Get the maximum number of connections.
     */
    public function getParallelUploads(): int
    {
        return $this->parallelUploads;
    }

    /**
     * Set the maximum number of connections.
     */
    public function setParallelUploads(int $parallelUploads): self
    {
        $this->parallelUploads = $parallelUploads;

        return $this;
    }

    /**
     * Return true if chunkUploads is enabled.
     */
    public function isChunkingEnabled(): bool
    {
        return $this->chunkUploads;
    }

    /**
     * Enable chunkUploads.
     */
    public function enableChunking(): self
    {
        $this->chunkUploads = true;

        return $this;
    }

    /**
     * Disable chunkUploads.
     */
    public function disableChunking(): self
    {
        $this->chunkUploads = false;

        return $this;
    }

    /**
     * Get the chunk size.
     */
    public function getChunkSize(): int
    {
        return $this->chunkSize;
    }

    /**
     * Set the chunk size.
     */
    public function setChunkSize(int $chunkSize): self
    {
        $this->chunkSize = $chunkSize;

        return $this;
    }

    /**
     * Return true if the direct upload is enabled.
     */
    public function isDirectUploadEnabled(): bool
    {
        return true === $this->directUpload && true === $this->storeFile;
    }

    /**
     * Enable the direct upload.
     */
    public function enableDirectUpload(): self
    {
        $this->directUpload = true;

        return $this;
    }

    /**
     * Disable the direct upload.
     */
    public function disableDirectUpload(): self
    {
        $this->directUpload = false;

        return $this;
    }

    /**
     * Return true if the store file is enabled.
     */
    public function isStoreFileEnabled(): bool
    {
        return $this->storeFile;
    }

    /**
     * Enable the store file.
     */
    public function enableStoreFile(): self
    {
        $this->storeFile = true;

        return $this;
    }

    /**
     * Disable the store file.
     */
    public function disableStoreFile(): self
    {
        $this->storeFile = false;

        return $this;
    }

    /**
     * Return true if the do not overwrite file is enabled.
     */
    public function isDoNotOverwriteEnabled(): bool
    {
        return $this->doNotOverwrite;
    }

    /**
     * Enable the do not overwrite file.
     */
    public function enableDoNotOverwrite(): self
    {
        $this->doNotOverwrite = true;

        return $this;
    }

    /**
     * Disable the do not overwrite file.
     */
    public function disableDoNotOverwrite(): self
    {
        $this->doNotOverwrite = false;

        return $this;
    }

    /**
     * Return true if the add file to database file system is enabled.
     */
    public function isAddToDbafsEnabled(): bool
    {
        return $this->addToDbafs;
    }

    /**
     * Enable the add file to database file system.
     */
    public function enableAddToDbafs(): self
    {
        $this->addToDbafs = true;

        return $this;
    }

    /**
     * Disable the add file to database file system.
     */
    public function disableAddToDbafs(): self
    {
        $this->addToDbafs = false;

        return $this;
    }

    /**
     * Get the upload folder.
     */
    public function getUploadFolder(): string
    {
        return $this->uploadFolder;
    }

    /**
     * Set the upload folder.
     */
    public function setUploadFolder(string $uploadFolder = ''): self
    {
        $this->uploadFolder = $uploadFolder;

        return $this;
    }

    /**
     * Get the labels.
     */
    public function getLabels(): array
    {
        return $this->labels;
    }

    /**
     * Set the labels.
     */
    public function setLabels(array $labels): self
    {
        $this->labels = $labels;

        return $this;
    }

    /**
     * Return true if the image resizing is enabled.
     */
    public function isImageResizingEnabled(): bool
    {
        return $this->imgResize;
    }

    /**
     * Enable the image resizing.
     */
    public function enableImageResizing(): self
    {
        $this->imgResize = true;

        return $this;
    }

    /**
     * Disable the image resizing.
     */
    public function disableImageResizing(): self
    {
        $this->imgResize = false;

        return $this;
    }

    /**
     * Get the image resize target width.
     */
    public function getImageResizeWidth(): int
    {
        return $this->imgResizeWidth;
    }

    /**
     * Set the image resize target width.
     */
    public function setImageResizeWidth(int $imgResizeWidth): self
    {
        $this->imgResizeWidth = $imgResizeWidth;

        return $this;
    }

    /**
     * Get the image resize target height.
     */
    public function getImageResizeHeight(): int
    {
        return $this->imgResizeHeight;
    }

    /**
     * Set the image resize target height.
     */
    public function setImageResizeHeight(int $imgResizeHeight): self
    {
        $this->imgResizeHeight = $imgResizeHeight;

        return $this;
    }

    /**
     * Return true if the client side image resizing is enabled.
     */
    public function isBrowserImageResizingEnabled(): bool
    {
        return $this->imgResizeBrowser;
    }

    /**
     * Enable the client side image resizing.
     */
    public function enableBrowserImageResizing(): self
    {
        $this->imgResizeBrowser = true;

        return $this;
    }

    /**
     * Disable the client side image resizing.
     */
    public function disableBrowserImageResizing(): self
    {
        $this->imgResizeBrowser = false;

        return $this;
    }

    /**
     * Get the image resize mode.
     */
    public function getBrowserImageResizeMode(): string
    {
        return $this->imgResizeModeBrowser;
    }

    /**
     * Set the image resize mode.
     */
    public function setBrowserImageResizeMode(string $imgResizeModeBrowser): self
    {
        $this->imgResizeModeBrowser = $imgResizeModeBrowser;

        return $this;
    }

    /**
     * Return true if the client side image resize upscaling is enabled.
     */
    public function isBrowserImageResizeUpscalingEnabled(): bool
    {
        return $this->imgResizeUpscaleBrowser;
    }

    /**
     * Enable client side image resize upscaling.
     */
    public function enableBrowserImageResizeUpscaling(): self
    {
        $this->imgResizeUpscaleBrowser = true;

        return $this;
    }

    /**
     * Disable client side image resize upscaling.
     */
    public function disableBrowserImageResizeUpscaling(): self
    {
        $this->imgResizeUpscaleBrowser = false;

        return $this;
    }
}
