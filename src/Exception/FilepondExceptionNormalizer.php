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

namespace Markocupic\ContaoFilepondUploader\Exception;

use Symfony\Contracts\Translation\TranslatorInterface;

class FilepondExceptionNormalizer implements FilepondExceptionNormalizerInterface
{
    public function __construct(
        private TranslatorInterface $translator,
    ) {
    }

    public function supports(\Throwable $exception): bool
    {
        return $exception instanceof TranslatableExceptionInterface;
    }

    public function normalize(\Throwable $exception): array
    {
        if ($exception instanceof TranslatableExceptionInterface) {
            /** @var TranslatableExceptionInterface $exception */
            $message = $this->translator->trans(
                $exception->getMessageKey(),
                $exception->getMessageData(),
                $exception->getMessageDomain(),
            );
        } else {
            $message = $exception->getMessage();
        }

        return [
            'error' => $message,
            'code' => $exception->getCode(),
        ];
    }
}
