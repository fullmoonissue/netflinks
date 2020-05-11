<?php

declare(strict_types=1);

namespace App\Domain\Newsletter;

use App\Domain\Entity\Link;
use App\Domain\Entity\Newsletter;

interface LastLinkGuesserInterface
{
    public function guess(Newsletter $newsletter): Link;
}
