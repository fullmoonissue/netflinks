<?php

declare(strict_types=1);

namespace App\Domain\Link;

use DateTimeImmutable;
use LogicException;

readonly class RangeLinkDates
{
    public function __construct(
        private DateTimeImmutable $startDate,
        private DateTimeImmutable $endDate,
    ) {
        if ($startDate > $endDate) {
            throw new LogicException('The start date must be before the end date');
        }
    }

    public function getStartDate(): DateTimeImmutable
    {
        return $this->startDate;
    }

    public function getEndDate(): DateTimeImmutable
    {
        return $this->endDate;
    }
}
