<?php

declare(strict_types=1);

namespace Tests\Double\Stub\Domain\Link;

use App\Domain\Entity\Link;
use App\Domain\Entity\Newsletter;
use App\Domain\Link\LinksExtractorInterface;

readonly class LinksExtractorStub implements LinksExtractorInterface
{
    /**
     * @param Link[] $links
     */
    public function __construct(
        private array $links,
    ) {
    }

    public function extract(Newsletter $newsletter): array
    {
        return $this->links;
    }
}
