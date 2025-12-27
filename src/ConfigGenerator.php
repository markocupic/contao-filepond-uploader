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
use Symfony\Component\DependencyInjection\Attribute\Autowire;

#[Autoconfigure(public: true)]
readonly class ConfigGenerator
{
    public function __construct(
        private ContaoCsrfTokenManager $csrfTokenManager,
        private Security $security,
        #[Autowire('%kernel.debug')]
        private bool $debug,
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

        // Enable the debug
        if ($this->debug) {
            $config->enableDebug();
        }

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
            'debug' => $config->isDebugEnabled(),
            'csrfToken' => $this->csrfTokenManager->getDefaultTokenValue(),
            'extensions' => $config->getExtensions(),
            'maxConnections' => $config->getMaxConnections(),
            'multiple' => $config->isMultiple(),
            'limit' => $config->isMultiple() ? $config->getFileLimit() : 1,
            'minSizeLimit' => $config->getMinSizeLimit(),
            'maxSizeLimit' => $config->getMaxSizeLimit(),
            'minImageWidth' => $config->getMinImageWidth(),
            'maxImageWidth' => $config->getMaxImageWidth(),
            'minImageHeight' => $config->getMinImageHeight(),
            'maxImageHeight' => $config->getMaxImageHeight(),
        ];

        // Enable client side image resizing
        if ($config->isImageResizingEnabled()) {
            $opt['allowImageResize'] = true;
            $opt['imageResizeTargetWidth'] = $config->getImageResizeTargetWidth() ?: Config::get('imageWidth');
            $opt['imageResizeTargetHeight'] = $config->getImageResizeTargetHeight() ?: Config::get('imageHeight');
            $opt['imageResizeMode'] = $config->getImageResizeMode();
            $opt['imageResizeUpscale'] = $config->isImageResizeUpscalingEnabled();
        }

        // Enable the chunking
        if ($config->isChunkingEnabled()) {
            $opt['chunking'] = true;
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
            'extensions' => static fn ($v) => $config->setExtensions($v),
            'multiple' => static fn ($v) => $v ? $config->enableMultiple() : $config->disableMultiple(),
            'mSize' => static fn ($v) => $config->setFileLimit((int) $v),
            'minlength' => static fn ($v) => $config->setMinSizeLimit($v),
            'maxlength' => static fn ($v) => $config->setMaxSizeLimit($v),
            'minImageWidth' => static fn ($v) => $config->setMinImageWidth($v),
            'maxImageWidth' => static fn ($v) => $config->setMaxImageWidth($v),
            'minImageHeight' => static fn ($v) => $config->setMinImageHeight($v),
            'maxImageHeight' => static fn ($v) => $config->setMaxImageHeight($v),
            'maxConnections' => static fn ($v) => $config->setMaxConnections($v),
            'chunking' => static fn ($v) => $v ? $config->enableChunking() : $config->disableChunking(),
            'chunkSize' => static fn ($v) => $config->setChunkSize((int) $v),
            'directUpload' => static fn ($v) => $v ? $config->enableDirectUpload() : $config->disableDirectUpload(),
            'storeFile' => static fn ($v) => $v ? $config->enableStoreFile() : $config->disableStoreFile(),
            'doNotOverwrite' => static fn ($v) => $v ? $config->enableDoNotOverwrite() : $config->disableDoNotOverwrite(),
            'addToDbafs' => static fn ($v) => $v ? $config->enableAddToDbafs() : $config->disableAddToDbafs(),
            'labels' => static fn ($v) => $config->setLabels(array_merge($config->getLabels(), $v)),
            'debug' => static fn ($v) => $v ? $config->enableDebug() : $config->disableDebug(),
            'allowImageResize' => static fn ($v) => $v ? $config->enableImageResize() : $config->disableImageResize(),
            'uploadFolder' => fn ($v) => $this->setUploadFolder($config, $v),
            'useHomeDir' => fn ($v) => $this->applyHomeDirIfAllowed($config, $v),
        ];

        foreach ($attributes as $key => $value) {
            if (isset($map[$key])) {
                $map[$key]($value);
                continue;
            }

            // Special cases
            if ('imageResizeTargetWidth' === $key && ($attributes['allowImageResize'] ?? false)) {
                $config->setImageResizeTargetWidth($value > 0 ? $value : Config::get('imageWidth'));
            }

            if ('imageResizeTargetHeight' === $key && ($attributes['allowImageResize'] ?? false)) {
                $config->setImageResizeTargetHeight($value > 0 ? $value : Config::get('imageHeight'));
            }

            if ('imageResizeMode' === $key && ($attributes['allowImageResize'] ?? false) && !empty($value)) {
                $config->setImageResizeMode($value);
            }

            if ('imageResizeUpscale' === $key && ($attributes['allowImageResize'] ?? false)) {
                $value ? $config->enableImageResizeUpscale() : $config->disableImageResizeUpscale();
            }
        }
    }

    private function applyHomeDirIfAllowed(UploaderConfig $config, bool $enabled): void
    {
        if (!$enabled) {
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

            // Set the path from model
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
            'labelMaxFileSizeExceeded',
            'labelMaxFileSize',
            'labelMaxTotalFileSizeExceeded',
            'labelMaxTotalFileSize',
            // Filepond Plugin: File validate type
            'labelFileTypeNotAllowed',
            'fileValidateTypeLabelExpectedTypes',
            // Filepond Plugin: Image validate size
            'imageValidateSizeLabelFormatError',
            'imageValidateSizeLabelImageSizeTooSmall',
            'imageValidateSizeLabelImageSizeTooBig',
            'imageValidateSizeLabelExpectedMinSize',
            'imageValidateSizeLabelExpectedMaxSize',
            'imageValidateSizeLabelImageResolutionTooLow',
            'imageValidateSizeLabelImageResolutionTooHigh',
            'imageValidateSizeLabelExpectedMinResolution',
            'imageValidateSizeLabelExpectedMaxResolution',
        ];

        $labels = [];

        foreach ($labelKeys as $key) {
            // Use label only if available, otherwise fall back to the default message
            // defined in Filepond JS script (EN)
            if (!empty($GLOBALS['TL_LANG']['MSC']['filepond.trans.'.$key])) {
                $labels['filepond'][$key] = $GLOBALS['TL_LANG']['MSC']['filepond.trans.'.$key];
            }
        }

        return $labels;
    }
}
