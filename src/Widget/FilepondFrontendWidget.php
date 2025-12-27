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

namespace Markocupic\ContaoFilepondUploader\Widget;

use Contao\Config;
use Contao\CoreBundle\Exception\ResponseException;
use Contao\System;
use Contao\UploadableWidgetInterface;
use Contao\Widget;
use Markocupic\ContaoFilepondUploader\AssetsManager;
use Markocupic\ContaoFilepondUploader\ConfigGenerator;
use Markocupic\ContaoFilepondUploader\RequestHandler\FrontendHandler;
use Markocupic\ContaoFilepondUploader\UploaderConfig;
use Markocupic\ContaoFilepondUploader\Validator;
use Markocupic\ContaoFilepondUploader\WidgetHelper;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

class FilepondFrontendWidget extends Widget implements UploadableWidgetInterface
{
    public const TYPE = 'filepondUploader';

    protected ContainerInterface $container;

    protected UploaderConfig|null $uploaderConfig = null;

    protected int $uploaderLimit = 1;

    protected array $jsConfig = [];

    protected $strTemplate = 'filepond_uploader_frontend';

    protected $strPrefix = 'widget widget-filepond-uploader';

    public function __construct(array|null $attributes = null)
    {
        // First run the parent constructor, then add our custom attributes
        parent::__construct($attributes);

        // Set defaults
        $this->arrConfiguration['maxlength'] = Config::get('maxFileSize') ?? 0; // max file size in bytes
        $this->arrConfiguration['minlength'] = 0; // min file size in bytes
        $this->arrConfiguration['maxImageWidth'] = Config::get('imageWidth') ?? 0; // max image width in pixels
        $this->arrConfiguration['maxImageHeight'] = Config::get('imageHeight') ?? 0; // max image height in pixels

        if (!empty($attributes)) {
            // Override defaults with values form field config.
            $attr = $attributes;
            $row = [];
            $row['maxlength'] = !empty($attr['maxlength']) ? $attr['maxlength'] : $this->arrConfiguration['maxlength']; // max file size in bytes
            $row['minlength'] = !empty($attr['minlength']) ? $attr['minlength'] : $this->arrConfiguration['minlength']; // min file size in bytes
            $row['maxImageWidth'] = !empty($attr['maxImageWidth']) ? $attr['maxImageWidth'] : $this->arrConfiguration['maxImageWidth']; // max image width in pixels
            $row['maxImageHeight'] = !empty($attr['maxImageHeight']) ? $attr['maxImageHeight'] : $this->arrConfiguration['maxImageHeight']; // max image height in pixels

            $this->arrConfiguration = array_merge($this->arrConfiguration, $row);
        }

        $this->blnSubmitInput = true;
        $this->container = System::getContainer();

        // Set the default attributes
        $this->setDefaultAttributes();
        $this->includeAssets();

        $request = $this->getRequest();

        if ($request->isXmlHttpRequest()) {
            /** @var FrontendHandler $frontendHandler */
            $frontendHandler = $this->container->get(FrontendHandler::class);
            $response = $frontendHandler->handleWidgetInitRequest($request, $this);

            if (null !== $response) {
                throw new ResponseException($response);
            }
        }
    }

    /**
     * Set the widget property.
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
            case 'maxImageWidth':
            case 'maxImageHeight':
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
            case 'directUpload':
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
            // DO NOT BREAK HERE:
            // We pass the value to the parent class too,
            // so it can perform its own processing.

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
     */
    public function parse($arrAttributes = null): string
    {
        if (!$this->jsConfig) {
            $this->jsConfig = $this->getConfigGenerator()->generateJavaScriptConfig($this->getUploaderConfig());
        }

        return parent::parse($arrAttributes);
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
     * This will convert the transfer keys to absolute file paths,
     * move the files to the destination directory and return the files array.
     *
     * Example return:
     *
     * ```php
     * Array
     * (
     *     [Filepond_0] => Array
     *         (
     *             [name] => picture_1.jpg
     *             [type] => image/jpeg
     *             [tmp_name] => /home/aeracing/public_html/contao5/files/filepond_test/picture_1.jpg
     *             [error] => 0
     *             [size] => 3421295
     *             [uuid] => 20e2c765-e2ab-11f0-b0b3-02000a14000a
     *             [uploaded] => 1
     *         )
     * )
     * ```
     */
    protected function validator(mixed $varInput): array|string
    {
        // This will transform the transfer keys to absolute file paths.
        // If directUpload is set to true, FilePond will not send any transfer keys.
        $files = $this->getWidgetHelper()->getFilesFromFileInputField($varInput);

        $isMultiple = true === $this->arrConfiguration['multiple'] ?? false;

        // If "multiple" is set, the input type is "array", otherwise "string or null.
        $varInput = match ($isMultiple) {
            true => $files,
            false => !empty($files[0]) ? $files[0] : null,
        };

        // This will move the files to the destination directory
        // and return the UUIDs or relative paths if addToDbafs is set to false.
        $files = $this->container->get(Validator::class)->validateInput($this, $varInput);

        // Returns a mock of the PHP $_FILES array that is used by Contao's Form instance.
        return $this->getWidgetHelper()->getFilesArray($this->strName, array_filter((array) $files), $this->storeFile);
    }

    protected function setDefaultAttributes(): void
    {
        $this->decodeEntities = true;

        // Set the uploader limit to 1 if it's not multiple
        if (empty($this->arrConfiguration['multiple'])) {
            $this->uploaderLimit = 1;
        }
    }

    protected function includeAssets(): void
    {
        $manager = $this->getAssetsManager();

        $allowImageResize = $this->arrConfiguration['allowImageResize'] ?? false;
        $assets = $manager->getFrontendAssets($allowImageResize);

        $manager->includeAssets($assets);
    }

    /**
     * @noinspection PhpIncompatibleReturnTypeInspection
     */
    protected function getConfigGenerator(): ConfigGenerator
    {
        return $this->container->get(ConfigGenerator::class);
    }

    /**
     * @noinspection PhpIncompatibleReturnTypeInspection
     */
    protected function getWidgetHelper(): WidgetHelper
    {
        return $this->container->get(WidgetHelper::class);
    }

    /**
     * @noinspection PhpIncompatibleReturnTypeInspection
     */
    protected function getAssetsManager(): AssetsManager
    {
        return $this->container->get(AssetsManager::class);
    }
}
