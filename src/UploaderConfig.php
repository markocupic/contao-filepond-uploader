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
     * Debug mode.
     */
    private bool $debug = false;

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
    private int $minSizeLimit = 0;

    /**
     * Maximum file size.
     */
    private int $maxSizeLimit = 0;

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
    private int $maxConnections = 3;

    /**
     * Allow chunking.
     */
    private bool $chunking = false;

    /**
     * Chunk size.
     */
    private int $chunkSize = 0;

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
    private string $uploadFolder = '';

    /**
     * Labels.
     */
    private array $labels = [];

    /**
     * Allow client side image resize.
     */
    private bool $allowImageResize = false;

    /**
     * Client side image resize target width (pixels).
     */
    private int $imageResizeTargetWidth = 0;

    /**
     * Client side image resize target height (pixels).
     */
    private int $imageResizeTargetHeight = 0;

    /**
     * Client side image resize mode.
     */
    private string $imageResizeMode = 'contain';

    /**
     * Client side image resize upscale.
     */
    private bool $imageResizeUpscale = false;

    /**
     * Return true if debug mode is enabled.
     */
    public function isDebugEnabled(): bool
    {
        return $this->debug;
    }

    /**
     * Enable debug.
     */
    public function enableDebug(): self
    {
        $this->debug = true;

        return $this;
    }

    /**
     * Disable debug.
     */
    public function disableDebug(): self
    {
        $this->debug = false;

        return $this;
    }

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
    public function getMinSizeLimit(): int
    {
        return $this->minSizeLimit;
    }

    /**
     * Set the minimum file size limit.
     */
    public function setMinSizeLimit($minSizeLimit): self
    {
        $this->minSizeLimit = (int) $minSizeLimit;

        return $this;
    }

    /**
     * Get the maximum file size limit.
     */
    public function getMaxSizeLimit(): int
    {
        return $this->maxSizeLimit;
    }

    /**
     * Set the maximum file size limit.
     */
    public function setMaxSizeLimit(int $maxSizeLimit): self
    {
        $this->maxSizeLimit = $maxSizeLimit;

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
    public function getMaxConnections(): int
    {
        return $this->maxConnections;
    }

    /**
     * Set the maximum number of connections.
     */
    public function setMaxConnections(int $maxConnections): self
    {
        $this->maxConnections = $maxConnections;

        return $this;
    }

    /**
     * Return true if chunking is enabled.
     */
    public function isChunkingEnabled(): bool
    {
        return $this->chunking;
    }

    /**
     * Enable chunking.
     */
    public function enableChunking(): self
    {
        $this->chunking = true;

        return $this;
    }

    /**
     * Disable chunking.
     */
    public function disableChunking(): self
    {
        $this->chunking = false;

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
        return $this->directUpload && $this->storeFile;
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
     * Return true if the client side image resizing is enabled.
     */
    public function isImageResizingEnabled(): bool
    {
        return $this->allowImageResize;
    }

    /**
     * Enable the client side image resizing.
     */
    public function enableImageResize(): self
    {
        $this->allowImageResize = true;

        return $this;
    }

    /**
     * Disable the client side image resizing.
     */
    public function disableImageResize(): self
    {
        $this->allowImageResize = false;

        return $this;
    }

    /**
     * Get the image resize target width.
     */
    public function getImageResizeTargetWidth(): int
    {
        return $this->imageResizeTargetWidth;
    }

    /**
     * Set the image resize target width.
     */
    public function setImageResizeTargetWidth(int $imageResizeTargetWidth): self
    {
        $this->imageResizeTargetWidth = $imageResizeTargetWidth;

        return $this;
    }

    /**
     * Get the image resize target height.
     */
    public function getImageResizeTargetHeight(): int
    {
        return $this->imageResizeTargetHeight;
    }

    /**
     * Set the image resize target height.
     */
    public function setImageResizeTargetHeight(int $imageResizeTargetHeight): self
    {
        $this->imageResizeTargetHeight = $imageResizeTargetHeight;

        return $this;
    }

    /**
     * Get the image resize mode.
     */
    public function getImageResizeMode(): string
    {
        return $this->imageResizeMode;
    }

    /**
     * Set the image resize mode.
     */
    public function setImageResizeMode(string $imageResizeMode): self
    {
        $this->imageResizeMode = $imageResizeMode;

        return $this;
    }

    /**
     * Return true if the client side image resize upscaling is enabled.
     */
    public function isImageResizeUpscalingEnabled(): bool
    {
        return $this->imageResizeUpscale;
    }

    /**
     * Enable client side image resize upscaling.
     */
    public function enableImageResizeUpscale(): self
    {
        $this->imageResizeUpscale = true;

        return $this;
    }

    /**
     * Disable client side image resize upscaling.
     */
    public function disableImageResizeUpscale(): self
    {
        $this->imageResizeUpscale = false;

        return $this;
    }
}
