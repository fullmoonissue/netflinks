<?php

declare(strict_types=1);

namespace Tests\Double\Stub\Domain\Link;

use App\Domain\Entity\Link;
use App\Domain\Entity\Newsletter;
use App\Domain\Link\IsLinkEligibleInterface;

readonly class IsLinkEligibleStub implements IsLinkEligibleInterface
{
    public function __construct(
        private bool $isLinkEligible,
    ) {
    }

    public function isSatisfiedBy(Link $link, Newsletter $newsletter): bool
    {
        return $this->isLinkEligible;
    }
}
