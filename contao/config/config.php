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

use Markocupic\ContaoFilepondUploader\Widget\FrontendWidget;

$GLOBALS['TL_FFL'][FrontendWidget::TYPE] = FrontendWidget::class;
