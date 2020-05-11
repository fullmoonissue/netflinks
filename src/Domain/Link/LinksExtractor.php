<?php

declare(strict_types=1);

namespace App\Domain\Link;

use App\Domain\Entity\Link;
use App\Domain\Entity\Newsletter;
use App\Domain\Repository\LinkRepositoryInterface;

readonly class LinksExtractor implements LinksExtractorInterface
{
    public function __construct(
        private LinkRepositoryInterface $linkRepository,
        private IsLinkEligibleInterface $isLinkEligible,
    ) {
    }

    /**
     * @return Link[]
     */
    public function extract(Newsletter $newsletter): array
    {
        return array_values(
            array_filter(
                $this->linkRepository->getRangedLinks(
                    $newsletter->getFirstLink()->getDate(),
                    $newsletter->getLastLink()->getDate(),
                ),
                function (Link $link) use ($newsletter) {
                    return $this->isLinkEligible->isSatisfiedBy($link, $newsletter);
                }
            )
        );
    }
}
