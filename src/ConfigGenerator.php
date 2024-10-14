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
use Contao\System;
use Contao\Validator;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

#[Autoconfigure(public: true)]
class ConfigGenerator
{
    public function __construct(
        private readonly ContaoCsrfTokenManager $csrfTokenManager,
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
        $this->setConfigFromAttributes($config, $attributes);

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
     * Generate the configuration array ready to use for JavaScript uploader setup.
     */
    public function generateJavaScriptConfig(UploaderConfig $config): array
    {
        $properties = [
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
            $properties['allowImageResize'] = true;
            $properties['imageResizeTargetWidth'] = $config->getImageResizeTargetWidth() ?: Config::get('imageWidth');
            $properties['imageResizeTargetHeight'] = $config->getImageResizeTargetHeight() ?: Config::get('imageHeight');
            $properties['imageResizeMode'] = $config->getImageResizeMode();
            $properties['imageResizeUpscale'] = $config->isImageResizeUpscalingEnabled();
        }

        // Enable the chunking
        if ($config->isChunkingEnabled()) {
            $properties['chunking'] = true;
            $properties['chunkSize'] = $config->getChunkSize();
            $properties['concurrent'] = $config->isConcurrentEnabled();
        }

        // Get labels
        $properties['translations'] = [];

        foreach ($config->getLabels()['filepond'] as $k => $v) {
            $properties['translations'][$k] = $v;
        }

        return $properties;
    }

    /**
     * Set the config from attributes.
     */
    private function setConfigFromAttributes(UploaderConfig $config, array $attributes): void
    {
        foreach ($attributes as $k => $v) {
            switch ($k) {
                case 'uploadFolder':
                    $this->setUploadFolder($config, (string) $v);
                    break;

                case 'useHomeDir':
                    if ($v && ($user = System::getContainer()->get('security.helper')->getUser()) instanceof FrontendUser) {
                        if ($user->assignDir && $user->homeDir) {
                            $this->setUploadFolder($config, $user->homeDir);
                        }
                    }
                    break;

                case 'extensions':
                    $config->setExtensions($v);
                    break;

                case 'multiple':
                    $v ? $config->enableMultiple() : $config->disableMultiple();
                    break;

                case 'mSize':
                    $config->setFileLimit((int) $v);
                    break;

                case 'minlength':
                    $config->setMinSizeLimit($v);
                    break;

                case 'maxlength':
                    $config->setMaxSizeLimit($v);
                    break;

                case 'minWidth':
                    $config->setMinImageWidth($v);
                    break;

                case 'maxWidth':
                    $config->setMaxImageWidth($v);
                    break;

                case 'minHeight':
                    $config->setMinImageHeight($v);
                    break;

                case 'maxHeight':
                    $config->setMaxImageHeight($v);
                    break;

                case 'maxConnections':
                    $config->setMaxConnections($v);
                    break;

                case 'chunking':
                    $v ? $config->enableChunking() : $config->disableChunking();
                    break;

                case 'chunkSize':
                    $config->setChunkSize((int) $v);
                    break;

                case 'concurrent':
                    $v ? $config->enableConcurrent() : $config->disableConcurrent();
                    break;

                case 'directUpload':
                    $v ? $config->enableDirectUpload() : $config->disableDirectUpload();
                    break;

                case 'storeFile':
                    $v ? $config->enableStoreFile() : $config->disableStoreFile();
                    break;

                case 'doNotOverwrite':
                    $v ? $config->enableDoNotOverwrite() : $config->disableDoNotOverwrite();
                    break;

                case 'addToDbafs':
                    $v ? $config->enableAddToDbafs() : $config->disableAddToDbafs();
                    break;

                case 'labels':
                    $config->setLabels(array_merge($config->getLabels(), $v));
                    break;

                case 'debug':
                    $v ? $config->enableDebug() : $config->disableDebug();
                    break;

                case 'allowImageResize':
                    $v ? $config->enableImageResize() : $config->disableImageResize();
                    break;

                case 'imageResizeTargetWidth':
                    if ($attributes['allowImageResize']) {
                        $width = !$v > 0 ? Config::get('imageWidth') : $v;
                        $config->setImageResizeTargetWidth($width);
                    }
                    break;

                case 'imageResizeTargetHeight':
                    if ($attributes['allowImageResize']) {
                        $height = !$v > 0 ? Config::get('imageHeight') : $v;
                        $config->setImageResizeTargetHeight($height);
                    }
                    break;

                case 'imageResizeMode':
                    if ($attributes['allowImageResize'] && !empty($v)) {
                        $config->setImageResizeMode($v);
                    }
                    break;

                case 'imageResizeUpscale':
                    if ($attributes['allowImageResize']) {
                        $v ? $config->enableImageResizeUpscale() : $config->disableImageResizeUpscale();
                    }
                    break;
            }
        }
    }

    /**
     * Set the upload folder.
     *
     * @param string $folder Can be a regular path or UUID
     */
    private function setUploadFolder(UploaderConfig $config, string $folder = ''): void
    {
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
            // Use label only if available, otherwise fall back to default message
            // defined in Filepond JS script (EN)
            if (!empty($GLOBALS['TL_LANG']['MSC']['filepond.trans.'.$key])) {
                $labels['filepond'][$key] = $GLOBALS['TL_LANG']['MSC']['filepond.trans.'.$key];
            }
        }

        return $labels;
    }
}
