<?php

declare(strict_types=1);

namespace App\Domain\Image;

use App\Domain\Entity\Image;

class IsImageExtensionEligible implements IsImageExtensionEligibleInterface
{
    public function isSatisfiedBy(Image $image): bool
    {
        $parts = explode('.', $image->getFilename());
        $extension = array_pop($parts);

        return in_array($extension, AllowedImageExtension::values());
    }
}
