<?php

declare(strict_types=1);

namespace App\Domain\Repository;

use App\Domain\Entity\Newsletter;

interface NewsletterRepositoryInterface
{
    public function add(Newsletter $entity, bool $flush = false): void;

    public function update(Newsletter $entity): void;

    public function remove(Newsletter $entity, bool $flush = false): void;
}
