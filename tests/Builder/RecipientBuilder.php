<?php

declare(strict_types=1);

namespace Tests\Builder;

use App\Domain\Entity\Recipient;
use Ramsey\Uuid\Uuid;

class RecipientBuilder
{
    private ?string $name = null;

    private ?string $shortName = null;

    public function withName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function withShortName(string $shortName): self
    {
        $this->shortName = $shortName;

        return $this;
    }

    public function build(): Recipient
    {
        return (new Recipient())
            ->setId(Uuid::uuid4()->toString())
            ->setName($this->name ?? Uuid::uuid4()->toString())
            ->setShort($this->shortName ?? Uuid::uuid4()->toString());
    }
}
