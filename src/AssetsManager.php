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

use Symfony\Component\Asset\Packages;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;

#[Autoconfigure(public: true)]
readonly class AssetsManager
{
    public function __construct(
        private Packages $packages,
    ) {
    }

    /**
     * Include the assets.
     */
    public function includeAssets(array $assets): void
    {
        foreach ($assets as $asset) {
            switch (pathinfo($asset, PATHINFO_EXTENSION)) {
                case 'css':
                    $GLOBALS['TL_CSS'][] = ltrim($asset, '/');
                    break;

                case 'js':
                    $GLOBALS['TL_JAVASCRIPT'][] = ltrim($asset, '/');
                    break;
            }
        }
    }

    /**
     * Get the frontend assets.
     */
    public function getFrontendAssets($allowImageResize = false): array
    {
        $assets = [];

        // CSS
        $assets[] = $this->packages->getUrl('frontend.css', 'markocupic_contao_filepond_uploader');

        // Filepond plugins
        $assets[] = $this->packages->getUrl('filepond-plugin-file-validate-size/dist/filepond-plugin-file-validate-size.js', 'markocupic_contao_filepond_uploader');
        $assets[] = $this->packages->getUrl('filepond-plugin-file-validate-type/dist/filepond-plugin-file-validate-type.js', 'markocupic_contao_filepond_uploader');
        $assets[] = $this->packages->getUrl('filepond-plugin-image-edit/dist/filepond-plugin-image-edit.js', 'markocupic_contao_filepond_uploader');
        $assets[] = $this->packages->getUrl('filepond-plugin-image-exif-orientation/dist/filepond-plugin-image-exif-orientation.js', 'markocupic_contao_filepond_uploader');
        $assets[] = $this->packages->getUrl('filepond-plugin-image-preview/dist/filepond-plugin-image-preview.js', 'markocupic_contao_filepond_uploader');
        $assets[] = $this->packages->getUrl('filepond-plugin-image-validate-size/dist/filepond-plugin-image-validate-size.js', 'markocupic_contao_filepond_uploader');

        if ($allowImageResize) {
            // Image resize plugin
            $assets[] = $this->packages->getUrl('filepond-plugin-image-resize/dist/filepond-plugin-image-resize.js', 'markocupic_contao_filepond_uploader');
            $assets[] = $this->packages->getUrl('filepond-plugin-image-transform/dist/filepond-plugin-image-transform.js', 'markocupic_contao_filepond_uploader');
        }

        // Add the core library as the last one!
        $assets[] = $this->packages->getUrl('filepond/dist/filepond.js', 'markocupic_contao_filepond_uploader');

        $assets[] = $this->packages->getUrl('contao-filepond-plugin.js', 'markocupic_contao_filepond_uploader');

        return $assets;
    }
}
