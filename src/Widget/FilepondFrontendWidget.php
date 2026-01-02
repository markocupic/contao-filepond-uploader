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

        $this->container = System::getContainer();

        // Set the default attributes
        $this->setDefaultAttributes($attributes ?? []);

        // Add the Filepond assets
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
            case 'imgResizeModeBrowser':
                $this->arrConfiguration[$strKey] = (string) $varValue;
                break;

            case 'mSize':
                if (isset($this->arrConfiguration['multiple']) && $this->arrConfiguration['multiple']) {
                    $this->arrConfiguration[$strKey] = (int) $varValue;
                }
                break;

            case 'parallelUploads':
            case 'maxImageWidth':
            case 'maxImageHeight':
                $this->arrConfiguration[$strKey] = (int) ($varValue ?? 0);
                break;

            case 'imgResizeBrowser':
                if (true === $varValue) {
                    if (!isset($this->arrConfiguration['imgResizeWidthBrowser'])) {
                        $this->arrConfiguration['imgResizeWidthBrowser'] = 1500;
                    }

                    if (!isset($this->arrConfiguration['imgResizeHeightBrowser'])) {
                        $this->arrConfiguration['imgResizeHeightBrowser'] = 1500;
                    }

                    if (!isset($this->arrConfiguration['imgResizeModeBrowser'])) {
                        $this->arrConfiguration['imgResizeModeBrowser'] = 'contain';
                    }

                    if (!isset($this->arrConfiguration['imgResizeUpscaleBrowser'])) {
                        $this->arrConfiguration['imgResizeUpscaleBrowser'] = false;
                    }
                }

                $this->arrConfiguration[$strKey] = (bool) $varValue;
                break;

            case 'chunkUploads':
            case 'doNotOverwrite':
            case 'useHomeDir':
            case 'imgResizeUpscaleBrowser':
            case 'imgResize':
            case 'imgResizeBrowser':
            case 'storeFile':
            case 'addToDbafs':
            case 'directUpload':
                $this->arrConfiguration[$strKey] = (bool) $varValue;
                break;

            case 'multiple':
                $this->arrConfiguration[$strKey] = (bool) $varValue;

                // Set the uploader limit to 1 if it's not multiple
                if (!$varValue) {
                    $this->arrConfiguration['mSize'] = 1;
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

    public function getMaximumUploadSize(): int
    {
        return $this->getConfiguration()['maxlength'] ?? 0;
    }

    public function getMinimumUploadSize(): int
    {
        return $this->getConfiguration()['minlength'] ?? 0;
    }

    protected function setDefaultAttributes(array $attributes): void
    {
        $this->arrConfiguration['name'] = !empty($attributes['name']) ? (string) $attributes['name'] : 'Filepond';
        $this->arrConfiguration['label'] = !empty($attributes['label']) ? (string) $attributes['label'] : 'Filepond';

        // If no upload path is set, use the default tmp path
        $this->arrConfiguration['uploadPath'] = empty($attributes['uploadPath']) ? System::getContainer()->getParameter('markocupic_contao_filepond_uploader.tmp_path') : (string) $attributes['uploadPath'];

        // Upload field (multiple or single)
        $this->arrConfiguration['mandatory'] = (bool) ($attributes['mandatory'] ?? false);

        // Set the uploader limit to 1 if it's not multiple
        if (empty($this->arrConfiguration['multiple'])) {
            $this->uploaderLimit = 1;
        }

        $this->blnSubmitInput = true;
        $this->decodeEntities = true;
    }

    /**
     * This will convert the transfer keys to absolute file paths,
     * move the files to the destination directory and return the $_FILES array.
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

        $isMultiple = true === ($this->arrConfiguration['multiple'] ?? false);

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

    protected function includeAssets(): void
    {
        $manager = $this->getAssetsManager();

        $imgResizeBrowser = $this->arrConfiguration['imgResizeBrowser'] ?? false;
        $assets = $manager->getFrontendAssets($imgResizeBrowser);

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
