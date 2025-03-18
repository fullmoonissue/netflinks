<?php

declare(strict_types=1);

namespace App\Domain\Newsletter;

use App\Domain\Entity\Image;
use App\Domain\Entity\Newsletter;

class AreImagesAssignedToRecipient
{
    public function isSatisfiedBy(Newsletter $newsletter): bool
    {
        /** @var Image $image */
        foreach ($newsletter->getImages() as $image) {
            if (!$this->isImageLinkedToRecipient($newsletter, $image)) {
                return false;
            }
        }

        return true;
    }

    private function isImageLinkedToRecipient(Newsletter $newsletter, Image $image): bool
    {
        $recipientId = $newsletter->getRecipient()->getId();

        foreach ($image->getRecipients() as $recipient) {
            if ($recipient->getId() === $recipientId) {
                return true;
            }
        }

        return false;
    }
}
