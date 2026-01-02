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
    public function getFrontendAssets($imgResizeBrowser = false): array
    {
        $assets = [];

        // CSS
        $assets[] = $this->packages->getUrl('frontend.css', 'markocupic_contao_filepond_uploader');

        // Add the customized filepond script!
        $assets[] = $this->packages->getUrl('frontend.js', 'markocupic_contao_filepond_uploader');

        return $assets;
    }
}
