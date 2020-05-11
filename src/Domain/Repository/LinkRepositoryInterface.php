<?php

declare(strict_types=1);

namespace App\Domain\Repository;

use App\Domain\Entity\Link;
use DateTimeImmutable;

interface LinkRepositoryInterface
{
    public function add(Link $entity, bool $flush = false): void;

    public function remove(Link $entity, bool $flush = false): void;

    public function getNextLink(DateTimeImmutable $date): Link;

    public function getLastLink(): Link;

    /**
     * @return Link[]
     */
    public function getRangedLinks(DateTimeImmutable $startDate, DateTimeImmutable $endDate): array;

    public function isUrlAlreadyRegistered(string $url): bool;
}
