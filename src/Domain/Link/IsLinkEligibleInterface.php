<?php

declare(strict_types=1);

namespace App\Domain\Link;

use App\Domain\Entity\Link;
use App\Domain\Entity\Newsletter;

interface IsLinkEligibleInterface
{
    public function isSatisfiedBy(Link $link, Newsletter $newsletter): bool;
}
