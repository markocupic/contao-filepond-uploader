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

namespace Markocupic\ContaoFilepondUploader\Tests;

use Contao\CoreBundle\Csrf\ContaoCsrfTokenManager;
use Contao\TestCase\ContaoTestCase;
use Markocupic\ContaoFilepondUploader\ConfigGenerator;
use Markocupic\ContaoFilepondUploader\UploaderConfig;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\SecurityBundle\Security;

/**
 * Tests for the ConfigGenerator class and its method generateFromWidgetAttributes.
 */
class ConfigGeneratorTest extends ContaoTestCase
{
    private MockObject $csrfTokenManager;

    private MockObject $security;

    private ConfigGenerator $configGenerator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->csrfTokenManager = $this->createMock(ContaoCsrfTokenManager::class);
        $this->security = $this->createMock(Security::class);

        $this->configGenerator = new ConfigGenerator(
            $this->csrfTokenManager,
            $this->security,
        );
    }

    public function testGenerateFromWidgetAttributesWithBasicAttributes(): void
    {
        $attributes = [
            'extensions' => 'jpg,png',
            'multiple' => true,
            'mSize' => 5,
        ];

        $uploaderConfig = $this->configGenerator->generateFromWidgetAttributes($attributes);

        $this->assertInstanceOf(UploaderConfig::class, $uploaderConfig);
        $this->assertSame('.jpg,.png', $uploaderConfig->getExtensions());
        $this->assertTrue($uploaderConfig->isMultiple());
        $this->assertSame(5, $uploaderConfig->getFileLimit());
        $this->assertSame('files', $uploaderConfig->getUploadFolder());
    }

    public function testGenerateFromWidgetAttributesWithImageAttributes(): void
    {
        $attributes = [
            'imgResize' => true,
            'imgResizeBrowser' => true,
            'imgResizeWidth' => 1920,
            'imgResizeHeight' => 1080,
            'imgResizeModeBrowser' => 'cover',
            'imgResizeUpscaleBrowser' => true,
        ];

        $uploaderConfig = $this->configGenerator->generateFromWidgetAttributes($attributes);

        $this->assertInstanceOf(UploaderConfig::class, $uploaderConfig);
        $this->assertTrue($uploaderConfig->isBrowserImageResizingEnabled());
        $this->assertSame(1920, $uploaderConfig->getImageResizeWidth());
        $this->assertSame(1080, $uploaderConfig->getImageResizeHeight());
        $this->assertSame('cover', $uploaderConfig->getBrowserImageResizeMode());
        $this->assertTrue($uploaderConfig->isBrowserImageResizeUpscalingEnabled());
    }

    public function testGenerateFromWidgetAttributesWithChunking(): void
    {
        $attributes = [
            'chunkUploads' => true,
            'chunkSize' => 1000000,
        ];

        $uploaderConfig = $this->configGenerator->generateFromWidgetAttributes($attributes);

        $this->assertInstanceOf(UploaderConfig::class, $uploaderConfig);
        $this->assertTrue($uploaderConfig->isChunkingEnabled());
        $this->assertSame(1000000, $uploaderConfig->getChunkSize());
    }

    public function testGenerateFromWidgetAttributesWithCustomUploadFolder(): void
    {
        $attributes = [
            'uploadFolder' => 'custom_upload_folder',
        ];

        $uploaderConfig = $this->configGenerator->generateFromWidgetAttributes($attributes);

        $this->assertInstanceOf(UploaderConfig::class, $uploaderConfig);
        $this->assertSame('custom_upload_folder', $uploaderConfig->getUploadFolder());
    }

    public function testGenerateFromWidgetAttributesWithMissingUploadFolder(): void
    {
        $attributes = [];

        $uploaderConfig = $this->configGenerator->generateFromWidgetAttributes($attributes);

        $this->assertInstanceOf(UploaderConfig::class, $uploaderConfig);
        $this->assertSame('files', $uploaderConfig->getUploadFolder());
    }

    public function testGenerateFromWidgetAttributesWithLabels(): void
    {
        // Mock the global language array that generateLabels() uses
        $GLOBALS['TL_LANG']['MSC']['filepond_trans_labelIdle'] = 'Drag & Drop your files here';

        $attributes = [];

        $uploaderConfig = $this->configGenerator->generateFromWidgetAttributes($attributes);

        $this->assertInstanceOf(UploaderConfig::class, $uploaderConfig);
        $labels = $uploaderConfig->getLabels();
        $this->assertArrayHasKey('filepond', $labels);
        $this->assertArrayHasKey('labelIdle', $labels['filepond']);
        $this->assertSame('Drag & Drop your files here', $labels['filepond']['labelIdle']);
    }

    public function testGenerateFromWidgetAttributesWithDirectUploadEnabled(): void
    {
        $attributes = [
            'directUpload' => true,
            'storeFile' => true,
        ];

        $uploaderConfig = $this->configGenerator->generateFromWidgetAttributes($attributes);

        $this->assertInstanceOf(UploaderConfig::class, $uploaderConfig);
        $this->assertTrue($uploaderConfig->isDirectUploadEnabled());
        $this->assertTrue($uploaderConfig->isStoreFileEnabled());
    }

    public function testGenerateFromWidgetAttributesWithDirectUploadDisabled(): void
    {
        $attributes = [
            'directUpload' => false,
            'storeFile' => true,
        ];

        $uploaderConfig = $this->configGenerator->generateFromWidgetAttributes($attributes);

        $this->assertInstanceOf(UploaderConfig::class, $uploaderConfig);
        $this->assertFalse($uploaderConfig->isDirectUploadEnabled());
    }

    public function testGenerateFromWidgetAttributesDirectUploadRequiresStoreFile(): void
    {
        // directUpload is true, but storeFile is false
        $attributes = [
            'directUpload' => true,
            'storeFile' => false,
        ];

        $uploaderConfig = $this->configGenerator->generateFromWidgetAttributes($attributes);

        $this->assertInstanceOf(UploaderConfig::class, $uploaderConfig);
        // isDirectUploadEnabled() returns only true if both flags are set
        $this->assertFalse($uploaderConfig->isDirectUploadEnabled());
    }

    public function testGenerateFromWidgetAttributesWithStoreFileOptions(): void
    {
        $attributes = [
            'storeFile' => true,
            'doNotOverwrite' => true,
            'addToDbafs' => true,
        ];

        $uploaderConfig = $this->configGenerator->generateFromWidgetAttributes($attributes);

        $this->assertInstanceOf(UploaderConfig::class, $uploaderConfig);
        $this->assertTrue($uploaderConfig->isStoreFileEnabled());
        $this->assertTrue($uploaderConfig->isDoNotOverwriteEnabled());
        $this->assertTrue($uploaderConfig->isAddToDbafsEnabled());
    }
}
