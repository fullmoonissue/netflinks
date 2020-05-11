<?php

declare(strict_types=1);

namespace App\Domain\Newsletter;

use App\Domain\Entity\Image;
use App\Domain\Entity\Newsletter;

class AreImagesAssignedToRecipient
{
    public function isSatisfiedBy(Newsletter $newsletter): bool
    {
        $recipientId = $newsletter->getRecipient()->getId();

        /** @var Image $image */
        foreach ($newsletter->getImages() as $image) {
            $isRecipientFound = false;
            foreach ($image->getRecipients() as $recipient) {
                if ($recipient->getId() === $recipientId) {
                    $isRecipientFound = true;
                    break;
                }
            }

            if (!$isRecipientFound) {
                return false;
            }
        }

        return true;
    }
}
