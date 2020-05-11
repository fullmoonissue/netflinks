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
        $recipientShortNames = array_map(
            function (Recipient $recipient) {
                return $recipient->getShort();
            },
            $link->getRecipients()->toArray()
        );

        if (!in_array($newsletter->getRecipient()->getShort(), $recipientShortNames)) {
            return false;
        }

        return $newsletter->getCategories()->contains($link->getCategory());
    }
}
