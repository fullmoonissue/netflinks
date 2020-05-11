<?php

declare(strict_types=1);

namespace App\Domain\Newsletter;

use App\Domain\Entity\Newsletter;
use App\Domain\Repository\LinkRepositoryInterface;
use App\Domain\Repository\NewsletterRepositoryInterface;

readonly class AssignNextLinks
{
    public function __construct(
        private LinkRepositoryInterface $linkRepository,
        private NewsletterRepositoryInterface $newsletterRepository,
        private LastLinkGuesser $lastLinkGuesser,
    ) {
    }

    public function assign(Newsletter $newsletter): void
    {
        $nextLink = $this->linkRepository->getNextLink($newsletter->getLastLink()->getDate());

        $newsletter->setFirstLink($nextLink);
        $newsletter->setLastLink(null);
        $newsletter->setLastLink($this->lastLinkGuesser->guess($newsletter));

        $this->newsletterRepository->update($newsletter);
    }
}
