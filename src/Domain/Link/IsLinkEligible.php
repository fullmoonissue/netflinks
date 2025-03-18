<?php

declare(strict_types=1);

namespace App\Domain\Link;

use App\Domain\Entity\Link;
use App\Domain\Entity\Newsletter;
use App\Domain\Entity\Recipient;

class IsLinkEligible implements IsLinkEligibleInterface
{
    public function isSatisfiedBy(Link $link, Newsletter $newsletter): bool
    {
        if (!$link->getRecipients()->contains($newsletter->getRecipient())) {
            return false;
        }

        return $newsletter->getCategories()->contains($link->getCategory());
    }
}
