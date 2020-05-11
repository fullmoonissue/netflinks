<?php

declare(strict_types=1);

namespace Tests\Double\Fake\Infrastructure\Repository;

use App\Domain\Entity\Image;
use App\Domain\Repository\ImageRepositoryInterface;

class ImageRepositoryFake implements ImageRepositoryInterface
{
    /**
     * @param Image[] $images
     */
    public function __construct(
        private array $images = [],
    ) {
    }

    public function add(Image $entity, bool $flush = false): void
    {
        $this->images[$entity->getId()] = $entity;
    }

    public function update(Image $entity): void
    {
        $this->images[$entity->getId()] = $entity;
    }

    public function remove(Image $entity, bool $flush = false): void
    {
        unset($this->images[$entity->getId()]);
    }

    public function exist(string $filename): bool
    {
        foreach ($this->images as $image) {
            if ($image->getFilename() === $filename) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return string[]
     */
    public function findIdsUsedByEveryone(): array
    {
        return [];
    }
}
