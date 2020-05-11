<?php

declare(strict_types=1);

namespace App\Domain\Repository;

use App\Domain\Entity\Image;

interface ImageRepositoryInterface
{
    public function add(Image $entity, bool $flush = false): void;

    public function update(Image $entity): void;

    public function remove(Image $entity, bool $flush = false): void;

    public function exist(string $filename): bool;

    /**
     * @return string[]
     */
    public function findIdsUsedByEveryone(): array;
}
