<?php

declare(strict_types=1);

namespace Tests\Builder;

use App\Domain\Entity\Category;
use Ramsey\Uuid\Uuid;

class CategoryBuilder
{
    private ?string $name = null;

    public function withName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function build(): Category
    {
        return (new Category())
            ->setId(Uuid::uuid4()->toString())
            ->setName($this->name ?? Uuid::uuid4()->toString());
    }
}
