<?php

declare(strict_types=1);

namespace Tests\Builder;

use App\Domain\Entity\Tag;
use Ramsey\Uuid\Uuid;

class TagBuilder
{
    private ?string $name = null;

    public function withName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function build(): Tag
    {
        return (new Tag())
            ->setId(Uuid::uuid4()->toString())
            ->setName($this->name ?? Uuid::uuid4()->toString());
    }
}
