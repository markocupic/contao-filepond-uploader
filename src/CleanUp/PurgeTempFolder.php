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

namespace Markocupic\ContaoFilepondUploader\CleanUp;

use Markocupic\ContaoFilepondUploader\Chunk\ChunkProcessor;
use Markocupic\ContaoFilepondUploader\TransferKey;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Path;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

readonly class PurgeTempFolder
{
    public function __construct(
        #[Autowire('%kernel.project_dir%')]
        private string $projectDir,
        #[Autowire('%markocupic_contao_filepond_uploader.tmp_path%')]
        private string $tmpPath,
        private Filesystem $filesystem,
        private LoggerInterface|null $contaoGeneralLogger = null,
        private LoggerInterface|null $contaoErrorLogger = null,
    ) {
    }

    public function run(): void
    {
        $tmpPath = Path::join($this->projectDir, $this->tmpPath);

        if (!is_dir($tmpPath)) {
            return;
        }

        // By materializing the results first, you guarantee that all directories are purged.
        $dirs = iterator_to_array($this->findDirectoriesFor($tmpPath));

        foreach ($dirs as $dir) {
            $this->removeDir($dir);
        }
    }

    private function findDirectoriesFor(string $tmpPath): iterable
    {
        return (new Finder())
            ->directories()
            ->in($tmpPath)
            ->name([TransferKey::PREFIX_FILE_UPLOAD.'_*', ChunkProcessor::PREFIX_CHUNK_UPLOAD.'_*'])
            ->date('<= now - 1 day')
        ;
    }

    private function removeDir(SplFileInfo $dir): void
    {
        $absPath = $dir->getRealPath();

        if (false === $absPath) {
            $this->contaoErrorLogger?->error(
                "Could not resolve real path for directory: '{$dir->getPathname()}'",
            );

            return;
        }

        $relPath = Path::makeRelative($absPath, $this->projectDir);

        try {
            $this->filesystem->remove($absPath);
        } catch (\Throwable $e) {
            $this->contaoErrorLogger?->error(
                "Could not remove FilePond temp upload directory: '$relPath'. Error: {$e->getMessage()}.",
            );

            return;
        }

        $this->contaoGeneralLogger?->info(
            "Removed FilePond temp directory: '$relPath'",
        );
    }
}
