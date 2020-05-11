<?php

declare(strict_types=1);

namespace App\Domain\Image;

use App\Domain\File\SkippableFile;
use App\Domain\Path\PublicPathGeneratorInterface;
use App\Domain\Repository\ImageRepositoryInterface;

readonly class UnregisteredImageExtractor
{
    public function __construct(
        private PublicPathGeneratorInterface $publicImagesPathGenerator,
        private ImageRepositoryInterface $imageRepository,
    ) {
    }

    /**
     * @return string[]
     */
    public function extract(): array
    {
        $images = scandir($this->publicImagesPathGenerator->generateAbsoluteFolderPath());
        $skippableFiles = SkippableFile::values();

        return array_values(
            array_filter(
                $images,
                function (string $imageFilename) use ($skippableFiles) {
                    if (in_array($imageFilename, $skippableFiles, true)) {
                        return false;
                    }

                    return !$this->imageRepository->exist($imageFilename);
                },
            )
        );
    }
}
