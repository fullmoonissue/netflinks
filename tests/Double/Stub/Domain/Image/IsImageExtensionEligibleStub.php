<?php

declare(strict_types=1);

namespace Tests\Double\Stub\Domain\Image;

use App\Domain\Entity\Image;
use App\Domain\Image\IsImageExtensionEligibleInterface;

readonly class IsImageExtensionEligibleStub implements IsImageExtensionEligibleInterface
{
    public function __construct(
        private bool $isImageExtensionEligible,
    ) {
    }

    public function isSatisfiedBy(Image $image): bool
    {
        return $this->isImageExtensionEligible;
    }
}
