<?php

declare(strict_types=1);

namespace App\Domain\File;

use App\Domain\BackedEnumValuesTrait;

enum SkippableFile: string
{
    use BackedEnumValuesTrait;

    case CURRENT_LOCATION = '.';
    case PARENT_LOCATION = '..';
    case GITKEEP_FILE = '.gitkeep';
    case DS_STORE = '.DS_Store';
}
