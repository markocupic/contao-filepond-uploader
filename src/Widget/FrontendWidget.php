<?php

declare(strict_types=1);

/*
 * This file is part of Contao Filepond Uploader.
 *
 * (c) Marko Cupic 2024 <m.cupic@gmx.ch>
 * @license GPL-3.0-or-later
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 * @link https://github.com/markocupic/contao-filepond-uploader
 */

namespace Markocupic\ContaoFilepondUploader\Widget;

use Contao\CoreBundle\Exception\ResponseException;
use Contao\FormFieldModel;
use Contao\System;
use Markocupic\ContaoFilepondUploader\AssetsManager;
use Markocupic\ContaoFilepondUploader\ConfigGenerator;
use Markocupic\ContaoFilepondUploader\RequestHandler\FrontendHandler;
use Markocupic\ContaoFilepondUploader\UploaderConfig;
use Markocupic\ContaoFilepondUploader\WidgetHelper;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

class FrontendWidget extends BaseWidget
{
    public const TYPE = 'filepondUploader';

    /**
     * Template.
     *
     * @var string
     */
    protected $strTemplate = 'filepond_uploader_frontend';

    /**
     * The CSS class prefix.
     *
     * @var string
     */
    protected $strPrefix = 'widget widget-filepond-uploader';

    protected ContainerInterface $container;

    /**
     * Initialize the object.
     *
     * @param array|null $attributes
     */
    public function __construct($attributes = null)
    {
        if (!empty($attributes['id']) && null !== ($formFieldModel = FormFieldModel::findByPk($attributes['id']))) {
            $this->arrConfiguration = array_merge($this->arrConfiguration, $formFieldModel->row());
        }

        $this->blnSubmitInput = true;

        $this->container = System::getContainer();

        parent::__construct($attributes);

        $request = $this->getRequest();

        // Set the default attributes
        $this->setDefaultAttributes();
        $this->includeAssets('frontend' === $request->attributes->get('_scope'));

        // Clean the chunks session when the widget is initialized in a non-ajax request
        if (!$request->isXmlHttpRequest()) {
            //$this->container->get('terminal42_fineuploader.chunk_uploader')->clearSession($this);
        }

        if ($request->isXmlHttpRequest()) {
            /** @var FrontendHandler $frontendHandler */
            $frontendHandler = $this->container->get(FrontendHandler::class);
            $response = $frontendHandler->handleWidgetInitRequest($request, $this);

            if (null !== $response) {
                throw new ResponseException($response);
            }
        }

        //$response = $this->container->get('terminal42_fineuploader.request.frontend_handler')->handleWidgetInitRequest(
        //$this->container->get('request_stack')->getCurrentRequest(),
        //$this
        //);

        //if (null !== $response) {
        //throw new ResponseException($response);
        //}
    }

    /**
     * Set the widget property.
     *
     * @param $strKey
     * @param $varValue
     */
    public function __set($strKey, $varValue): void
    {
        switch ($strKey) {
            case 'extensions':
            case 'imageResizeMode':
                $this->arrConfiguration[$strKey] = (string) $varValue;
                break;

            case 'mSize':
                if (isset($this->arrConfiguration['multiple']) && $this->arrConfiguration['multiple']) {
                    $this->arrConfiguration[$strKey] = (int) $varValue;
                }
                break;

            case 'maxConnections':
            case 'maxWidth':
            case 'maxHeight':
            case 'imageResizeTargetWidth':
            case 'imageResizeTargetHeight':
                $this->arrConfiguration[$strKey] = (int) $varValue ?? 0;
                break;

            case 'allowImageResize':
                if (true === $varValue) {
                    if (!isset($this->arrConfiguration['imageResizeTargetWidth'])) {
                        $this->arrConfiguration['imageResizeTargetWidth'] = 1500;
                    }

                    if (!isset($this->arrConfiguration['imageResizeTargetHeight'])) {
                        $this->arrConfiguration['imageResizeTargetHeight'] = 1500;
                    }

                    if (!isset($this->arrConfiguration['imageResizeMode'])) {
                        $this->arrConfiguration['imageResizeMode'] = 'contain';
                    }

                    if (!isset($this->arrConfiguration['imageResizeUpscale'])) {
                        $this->arrConfiguration['imageResizeUpscale'] = false;
                    }
                }

                $this->arrConfiguration[$strKey] = (bool) $varValue;
                break;
            case 'imageResizeUpscale':
            case 'storeFile':
            case 'addToDbafs':
                $this->arrConfiguration[$strKey] = (bool) $varValue;
                break;

            case 'multiple':
                $this->arrConfiguration[$strKey] = (bool) $varValue;

                // Set the uploader limit to 1 if it's not multiple
                if (!$varValue) {
                    $this->uploaderLimit = 1;
                }
                break;

            /** @noinspection PhpMissingBreakStatementInspection */
            case 'mandatory':
                if ($varValue) {
                    $this->arrAttributes['required'] = 'required';
                } else {
                    unset($this->arrAttributes['required']);
                }
            // DO NOT BREAK HERE

            // no break
            default:
                parent::__set($strKey, $varValue);
        }
    }

    public function getRequest(): Request
    {
        return $this->container->get('request_stack')->getCurrentRequest();
    }

    /**
     * Parse the template file and return it as string.
     *
     * @param array|null $arrAttributes An optional attributes array
     *
     * @return string The template markup
     */
    public function parse($arrAttributes = null): string
    {
        // Initiate the session if chunking is enabled (#86).
        //if ($this->getUploaderConfig()->isChunkingEnabled()) {
        ///** @var ChunkUploader $chunkUploader */
        //$chunkUploader = $this->container->get('terminal42_fineuploader.chunk_uploader');
        //$chunkUploader->initSession($this);
        //}

        if (!$this->jsConfig) {
            $this->jsConfig = $this->getConfigGenerator()->generateJavaScriptConfig($this->getUploaderConfig());
        }

        return parent::parse($arrAttributes);
    }

    /**
     * Get the uploader config.
     */
    public function getUploaderConfig(): UploaderConfig
    {
        if (null === $this->uploaderConfig) {
            $this->uploaderConfig = $this->getConfigGenerator()->generateFromWidgetAttributes($this->arrConfiguration);
        }

        return $this->uploaderConfig;
    }

    /**
     * Get the widget configuration.
     */
    public function getConfiguration(): array
    {
        return $this->arrConfiguration;
    }

    /**
     * Required by \Contao\Widget class. Use the parse() method instead.
     *
     * @throws \BadMethodCallException
     */
    public function generate(): void
    {
        throw new \BadMethodCallException('Use the parse() method instead');
    }

    /**
     * @param string|array|null $varInput
     */
    protected function validator(mixed $varInput): array|string
    {
        $files = $this->getWidgetHelper()->getFilesFromFileInputField($varInput);

        $isMultiple = !empty($this->arrConfiguration['multiple']) && true === $this->arrConfiguration['multiple'];

        // If "multiple" is set the input type "array", otherwise "string".
        $files = parent::validator($isMultiple ? $files : (!empty($files[0]) ? $files[0] : ''));

        return $this->getWidgetHelper()->getFilesArray($this->strName, array_filter((array) $files), $this->storeFile);
    }

    /**
     * Set the default attributes.
     */
    protected function setDefaultAttributes(): void
    {
        $this->decodeEntities = true;

        // Set the uploader limit to 1 if it's not multiple
        if (empty($this->arrConfiguration['multiple'])) {
            $this->uploaderLimit = 1;
        }
    }

    /**
     * Include the assets.
     */
    protected function includeAssets(bool $frontendAssets = true): void
    {
        $manager = $this->getAssetsManager();
        $assets = $manager->getBasicAssets();

        if ($frontendAssets) {
            $assets = array_merge($assets, $manager->getFrontendAssets($this->arrConfiguration['allowImageResize'] ?? false));
        }

        $manager->includeAssets($assets);
    }

    /**
     * Get the config generator.
     */
    protected function getConfigGenerator(): ConfigGenerator
    {
        /** @var ConfigGenerator $configGenerator */
        return $this->container->get(ConfigGenerator::class);
    }

    /**
     * Get the widget helper.
     */
    protected function getWidgetHelper(): WidgetHelper
    {
        /** @var WidgetHelper $widgetHelper */
        return $this->container->get(WidgetHelper::class);
    }

    /**
     * Get the assets manager.
     */
    protected function getAssetsManager(): AssetsManager
    {
        /** @var AssetsManager $assetManager */
        return $this->container->get(AssetsManager::class);
    }
}
