<?php

declare(strict_types=1);

namespace App\Domain\Newsletter;

use App\Domain\Entity\Link;
use App\Domain\Entity\Newsletter;
use App\Domain\Repository\LinkRepositoryInterface;
use Throwable;

readonly class LastLinkGuesser implements LastLinkGuesserInterface
{
    public function __construct(
        private LinkRepositoryInterface $linkRepository,
    ) {
    }

    public function guess(Newsletter $newsletter): Link
    {
        try {
            $lastLink = $newsletter->getLastLink();
        } catch (Throwable) {
            $lastLink = $this->linkRepository->getLastLink();
        }

        return $lastLink;
    }
}
