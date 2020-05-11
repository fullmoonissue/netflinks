<?php

declare(strict_types=1);

namespace App\Domain\Image;

use App\Domain\BackedEnumValuesTrait;

enum AllowedImageExtension: string
{
    use BackedEnumValuesTrait;

    case JPEG = 'jpeg';
    case JPG = 'jpg';
    case PNG = 'png';
    case WEBP = 'webp';
}
