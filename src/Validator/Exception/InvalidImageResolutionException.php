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

class InvalidImageResolutionException extends \RuntimeException implements TranslatableExceptionInterface
{
    public function __construct(
        string $reason,
        private readonly string $translatableText,
        private readonly array $params = [],
    ) {
        parent::__construct($reason);
    }

    public function getTranslatableText(): string
    {
        return $this->translatableText;
    }

    public function getParams(): array
    {
        return $this->params;
    }
}
