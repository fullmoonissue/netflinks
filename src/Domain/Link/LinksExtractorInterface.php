<?php

declare(strict_types=1);

namespace App\Domain\Link;

use App\Domain\Entity\Link;
use App\Domain\Entity\Newsletter;

interface LinksExtractorInterface
{
    /**
     * @return Link[]
     */
    public function extract(Newsletter $newsletter): array;
}
