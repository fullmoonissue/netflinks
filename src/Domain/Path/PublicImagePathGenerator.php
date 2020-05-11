<?php

declare(strict_types=1);

namespace App\Domain\Path;

use App\Domain\Entity\Image;

class PublicImagePathGenerator implements PublicPathGeneratorInterface
{
    public const string PUBLIC_IMAGE_RELATIVE_FOLDER_PATH = '/assets/images';

    public function __construct(
        private readonly PublicPathGenerator $publicPathGenerator,
    ) {
    }

    public function generateRelativeFolderPath(): string
    {
        return sprintf(
            '%s/%s',
            $this->publicPathGenerator->getRelativeFolderPath(),
            ltrim(self::PUBLIC_IMAGE_RELATIVE_FOLDER_PATH, '/')
        );
    }

    public function generateAbsoluteFolderPath(): string
    {
        return sprintf(
            '%s/%s',
            $this->publicPathGenerator->generateAbsoluteFolderPath(),
            ltrim(self::PUBLIC_IMAGE_RELATIVE_FOLDER_PATH, '/')
        );
    }

    public function generateAbsoluteFilePath(Image $image): string
    {
        return sprintf(
            '%s/%s',
            $this->generateAbsoluteFolderPath(),
            $image->getFilename()
        );
    }
}
