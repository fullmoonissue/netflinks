<?php

declare(strict_types=1);

namespace App\Domain\Path;

interface PublicPathGeneratorInterface
{
    public function generateAbsoluteFolderPath(): string;
}
