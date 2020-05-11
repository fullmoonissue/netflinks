<?php

declare(strict_types=1);

namespace App\Domain\Newsletter;

use App\Domain\Entity\Link;
use App\Domain\Entity\Newsletter;
use App\Domain\Link\LinksExtractorInterface;

readonly class RequestedImagesCounter
{
    public function __construct(
        private LinksExtractorInterface $linksExtractor,
    ) {
    }

    public function count(Newsletter $newsletter): int
    {
        $links = $this->linksExtractor->extract($newsletter);

        return count(
            array_reduce(
                $links,
                function ($currentCategories, Link $link) {
                    $currentLinkCategory = $link->getCategory()->getName();
                    if (!isset($currentCategories[$currentLinkCategory])) {
                        $currentCategories[$currentLinkCategory] = true;
                    }

                    return $currentCategories;
                },
                []
            )
        );
    }
}
