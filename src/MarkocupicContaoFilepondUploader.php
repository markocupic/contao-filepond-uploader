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

use Markocupic\ContaoFilepondUploader\DependencyInjection\MarkocupicContaoFilepondUploaderExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class MarkocupicContaoFilepondUploader extends Bundle
{
    public function getPath(): string
    {
        return \dirname(__DIR__);
    }

    public function getContainerExtension(): MarkocupicContaoFilepondUploaderExtension
    {
        return new MarkocupicContaoFilepondUploaderExtension();
    }

    public function build(ContainerBuilder $container): void
    {
        parent::build($container);
    }
}
