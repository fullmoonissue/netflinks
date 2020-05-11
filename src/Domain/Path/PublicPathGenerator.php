<?php

declare(strict_types=1);

namespace App\Domain\Path;

class PublicPathGenerator implements PublicPathGeneratorInterface
{
    private const string PUBLIC_RELATIVE_FOLDER_PATH = '/public';

    public function __construct(
        private readonly string $projectDirectory,
    ) {
    }

    public function getRelativeFolderPath(): string
    {
        return self::PUBLIC_RELATIVE_FOLDER_PATH;
    }

    public function generateAbsoluteFolderPath(): string
    {
        return sprintf(
            '%s/%s',
            $this->projectDirectory,
            ltrim(self::PUBLIC_RELATIVE_FOLDER_PATH, '/'),
        );
    }
}
