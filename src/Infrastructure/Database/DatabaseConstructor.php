<?php

declare(strict_types=1);

namespace App\Infrastructure\Database;

use Symfony\Component\Filesystem\Filesystem;

class DatabaseConstructor
{
    public function construct(string $path): void
    {
        $fs = new Filesystem();
        if (!$fs->exists($path)) {
            $fs->touch($path);
        }
    }
}
