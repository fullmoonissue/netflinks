<?php

declare(strict_types=1);

namespace App\Domain\Image;

use App\Domain\Entity\Image;

interface IsImageExtensionEligibleInterface
{
    public function isSatisfiedBy(Image $image): bool;
}
