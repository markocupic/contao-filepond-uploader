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

use Contao\UploadableWidgetInterface;
use Contao\Widget;
use Markocupic\ContaoFilepondUploader\UploaderConfig;
use Markocupic\ContaoFilepondUploader\Validator;
use Symfony\Component\DependencyInjection\ContainerInterface;

abstract class BaseWidget extends Widget implements UploadableWidgetInterface
{
    protected ContainerInterface $container;

    protected UploaderConfig|null $uploaderConfig = null;

    protected int $uploaderLimit = 1;

    protected array $jsConfig = [];

    /**
     * Return an array of paths if $this->multiple is set,
     * otherwise return a string with the filepath.
     *
     * @param string|array $varInput
     */
    protected function validator(mixed $varInput): array|string
    {
        return $this->container->get(Validator::class)->validateInput($this, $varInput);
    }
}
