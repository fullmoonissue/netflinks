<?php

declare(strict_types=1);

namespace App\Domain\Newsletter;

use App\Domain\Entity\Image;
use App\Domain\Entity\Newsletter;
use App\Domain\Repository\ImageRepositoryInterface;

readonly class AssignImagesToRecipient
{
    public function __construct(
        private ImageRepositoryInterface $imageRepository,
    ) {
    }

    public function assign(Newsletter $newsletter): void
    {
        $nlRecipient = $newsletter->getRecipient();

        /** @var Image $image */
        foreach ($newsletter->getImages() as $image) {
            $isRecipientFound = false;
            foreach ($image->getRecipients() as $recipient) {
                if ($recipient->getId() === $nlRecipient->getId()) {
                    $isRecipientFound = true;
                    break;
                }
            }
            if (!$isRecipientFound) {
                $image->addRecipient($nlRecipient);
                $this->imageRepository->update($image);
            }
        }
    }
}
