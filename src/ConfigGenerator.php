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
use Contao\CoreBundle\Csrf\ContaoCsrfTokenManager;
use Contao\FilesModel;
use Contao\FrontendUser;
use Contao\Validator;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Symfony\Component\HttpFoundation\File\UploadedFile;

#[Autoconfigure(public: true)]
readonly class ConfigGenerator
{
    public function __construct(
        private ContaoCsrfTokenManager $csrfTokenManager,
        private Security $security,
    ) {
    }

    /**
     * Generate the configuration from widget attributes.
     */
    public function generateFromWidgetAttributes(array $attributes): UploaderConfig
    {
        $config = new UploaderConfig();

        // Set the config from attributes
        $this->buildConfigurationFromAttributes($config, $attributes);

        // Set the upload folder to the default one if not set yet
        if (!$config->getUploadFolder()) {
            $this->setUploadFolder($config, (string) Config::get('uploadPath'));
        }

        $config->setLabels($this->generateLabels());

        return $config;
    }

    /**
     * Generate the option array ready to use for JavaScript uploader setup.
     */
    public function generateJavaScriptConfig(UploaderConfig $config): array
    {
        $opt = [
            'csrfToken' => $this->csrfTokenManager->getDefaultTokenValue(),
            'extensions' => $config->getExtensions(),
            'parallelUploads' => $config->getParallelUploads(),
            'multiple' => $config->isMultiple(),
            'limit' => $config->isMultiple() ? $config->getFileLimit() : 1,
            'minFileSizeLimit' => $config->getMinFileSizeLimit(),
            'maxFileSizeLimit' => $config->getMaxFileSizeLimit(),
            'minImageWidth' => $config->getMinImageWidth(),
            'maxImageWidth' => $config->getMaxImageWidth(),
            'minImageHeight' => $config->getMinImageHeight(),
            'maxImageHeight' => $config->getMaxImageHeight(),
            'imgResize' => $config->isImageResizingEnabled(),
        ];

        // Enable image resizing
        if ($config->isImageResizingEnabled()) {
            $opt['imgResizeWidth'] = $config->getImageResizeWidth() ?: Config::get('imageWidth');
            $opt['imgResizeHeight'] = $config->getImageResizeHeight() ?: Config::get('imageHeight');
        }

        // Enable client side image resizing
        $opt['imgResizeBrowser'] = false;

        if ($config->isBrowserImageResizingEnabled() && $config->isImageResizingEnabled()) {
            $opt['imgResizeBrowser'] = true;
            $opt['imgResizeModeBrowser'] = $config->getBrowserImageResizeMode();
            $opt['imgResizeUpscaleBrowser'] = $config->isBrowserImageResizeUpscalingEnabled();
        }

        // Enable the chunkUploads
        if ($config->isChunkingEnabled()) {
            $opt['chunkUploads'] = true;
            $opt['chunkSize'] = $config->getChunkSize();
        }

        // Get labels
        $opt['translations'] = [];

        foreach ($config->getLabels()['filepond'] as $k => $v) {
            $opt['translations'][$k] = $v;
        }

        return $opt;
    }

    /**
     * Set the config from attributes.
     */
    private function buildConfigurationFromAttributes(UploaderConfig $config, array $attributes): void
    {
        $map = [
            'extensions' => static fn ($v) => $config->setExtensions((string) ($attributes['extensions'] ?? Config::get('uploadTypes'))),
            'multiple' => static fn ($v) => true === $v ? $config->enableMultiple() : $config->disableMultiple(),
            'mSize' => static fn ($v) => $config->setFileLimit((int) $v),
            'minImageWidth' => static fn ($v) => $config->setMinImageWidth((int) $v),
            'minImageHeight' => static fn ($v) => $config->setMinImageHeight((int) $v),
            'maxImageWidth' => static fn ($v) => $config->setMaxImageWidth((int) $v > 0 ? (int) $v : Config::get('imageWidth')),
            'maxImageHeight' => static fn ($v) => $config->setMaxImageHeight((int) $v > 0 ? (int) $v : Config::get('imageHeight')),
            'parallelUploads' => static fn ($v) => $config->setParallelUploads((int) $v),
            'chunkUploads' => static fn ($v) => true === $v ? $config->enableChunking() : $config->disableChunking(),
            'chunkSize' => static fn ($v) => $config->setChunkSize((int) $v),
            'directUpload' => static fn ($v) => true === $v ? $config->enableDirectUpload() : $config->disableDirectUpload(),
            'storeFile' => static fn ($v) => true === $v ? $config->enableStoreFile() : $config->disableStoreFile(),
            'doNotOverwrite' => static fn ($v) => true === $v ? $config->enableDoNotOverwrite() : $config->disableDoNotOverwrite(),
            'addToDbafs' => static fn ($v) => true === $v ? $config->enableAddToDbafs() : $config->disableAddToDbafs(),
            'labels' => static fn ($v) => $config->setLabels(array_merge($config->getLabels(), $v)),
            'imgResize' => static fn ($v) => true === $v ? $config->enableImageResizing() : $config->disableImageResizing(),
            'imgResizeBrowser' => static fn ($v) => true === $v ? $config->enableBrowserImageResizing() : $config->disableBrowserImageResizing(),
            'uploadFolder' => fn ($v) => $this->setUploadFolder($config, $v),
            'useHomeDir' => fn ($v) => $this->applyHomeDirIfAllowed($config, $v),
        ];

        foreach ($attributes as $key => $value) {
            if (isset($map[$key])) {
                $map[$key]($value);
                continue;
            }

            // Special cases
            if ('minlength' === $key) {
                $v = !empty($attributes['minlength']) ? $attributes['minlength'] : 0; // max file size in bytes
                $config->setMinFileSizeLimit((int) $v);
            }

            if ('maxlength' === $key) {
                $v = !empty($attributes['maxlength']) ? $attributes['maxlength'] : min(UploadedFile::getMaxFilesize(), Config::get('maxFileSize')); // max file size in bytes
                $config->setMaxFileSizeLimit((int) $v);
            }

            if ('imgResizeWidth' === $key && true === ($attributes['imgResize'] ?? false)) {
                $config->setImageResizeWidth($value > 0 ? (int) $value : (int) Config::get('imageWidth'));
            }

            if ('imgResizeHeight' === $key && true === ($attributes['imgResize'] ?? false)) {
                $config->setImageResizeHeight($value > 0 ? (int) $value : (int) Config::get('imageHeight'));
            }

            if ('imgResizeModeBrowser' === $key && true === ($attributes['imgResizeBrowser'] ?? false) && !empty($value)) {
                $config->setBrowserImageResizeMode($value);
            }

            if ('imgResizeUpscaleBrowser' === $key && true === ($attributes['imgResizeBrowser'] ?? false)) {
                $value ? $config->enableBrowserImageResizeUpscaling() : $config->disableBrowserImageResizeUpscaling();
            }
        }
    }

    private function applyHomeDirIfAllowed(UploaderConfig $config, bool $enabled): void
    {
        if (false === $enabled) {
            return;
        }

        $user = $this->security->getUser();

        if ($user instanceof FrontendUser && $user->assignDir && $user->homeDir) {
            $this->setUploadFolder($config, $user->homeDir);
        }
    }

    /**
     * Set the upload folder.
     *
     * @param string $folder Can be a regular path or UUID
     */
    private function setUploadFolder(UploaderConfig $config, string $folder = ''): void
    {
        // Will check if the folder is a valid string or binary UUID
        // and convert it to a relative path
        if (Validator::isUuid($folder)) {
            $model = FilesModel::findByUuid($folder);

            // Set the path from the model
            if (null !== $model) {
                $config->setUploadFolder($model->path);
            }
        } else {
            $config->setUploadFolder($folder);
        }
    }

    /**
     * Generate the labels for the uploader.
     */
    private function generateLabels(): array
    {
        $labelKeys = [
            // Filepond
            'labelIdle',
            'labelInvalidField',
            'labelFileWaitingForSize',
            'labelFileSizeNotAvailable',
            'labelFileLoading',
            'labelFileLoadError',
            'labelFileProcessing',
            'labelFileProcessingComplete',
            'labelFileProcessingAborted',
            'labelFileProcessingError',
            'labelFileProcessingRevertError',
            'labelFileRemoveError',
            'labelTapToCancel',
            'labelTapToRetry',
            'labelTapToUndo',
            'labelButtonRemoveItem',
            'labelButtonAbortItemLoad',
            'labelButtonRetryItemLoad',
            'labelButtonAbortItemProcessing',
            'labelButtonUndoItemProcessing',
            'labelButtonRetryItemProcessing',
            'labelButtonProcessItem',
            // Filepond Plugin: File validate size
            'labelMaxFileSizeError',
            'labelMinFileSizeError',
            // Filepond Plugin: File validate type
            'labelFileTypeNotAllowed',
            'fileValidateTypeLabelExpectedTypes',
            // Custom Filepond Plugin: Validate image resolution
            'labelMinImageResolutionValidationError',
            'labelMaxImageResolutionValidationError',
            'labelImageValidateSizeLabelFormatError',
        ];

        $labels = [];

        foreach ($labelKeys as $key) {
            // Use label only if available, otherwise fall back to the default message
            // defined in Filepond JS script (EN)
            if (!empty($GLOBALS['TL_LANG']['MSC']['filepond_trans_'.$key])) {
                $labels['filepond'][$key] = $GLOBALS['TL_LANG']['MSC']['filepond_trans_'.$key];
            }
        }

        return $labels;
    }
}
