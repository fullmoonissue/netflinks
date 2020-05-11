<?php

declare(strict_types=1);

namespace Tests\Double\Stub\Application\Path;

use App\Domain\Path\PublicPathGeneratorInterface;

readonly class PublicPathGeneratorStub implements PublicPathGeneratorInterface
{
    public function __construct(
        private string $absoluteFolderPath,
    ) {
    }

    public function generateAbsoluteFolderPath(): string
    {
        return $this->absoluteFolderPath;
    }
}
