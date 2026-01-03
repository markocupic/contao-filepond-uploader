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

namespace Markocupic\ContaoFilepondUploader\Validator\Exception;

use Markocupic\ContaoFilepondUploader\Exception\AbstractTranslatedException;
use Markocupic\ContaoFilepondUploader\Exception\TranslatableExceptionInterface;

class NoFileUploadedException extends AbstractTranslatedException implements TranslatableExceptionInterface
{
}
