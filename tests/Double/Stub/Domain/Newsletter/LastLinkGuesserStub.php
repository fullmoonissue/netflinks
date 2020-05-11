<?php

declare(strict_types=1);

namespace Tests\Double\Stub\Domain\Newsletter;

use App\Domain\Entity\Link;
use App\Domain\Entity\Newsletter;
use App\Domain\Newsletter\LastLinkGuesserInterface;

readonly class LastLinkGuesserStub implements LastLinkGuesserInterface
{
    public function __construct(
        private Link $link,
    ) {
    }

    public function guess(Newsletter $newsletter): Link
    {
        return $this->link;
    }
}
