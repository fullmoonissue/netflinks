<?php

declare(strict_types=1);

namespace App\Infrastructure\Validator;

use Attribute;
use Symfony\Component\Validator\Constraint;

#[Attribute]
class ContainsCorrectDateRange extends Constraint
{
    public string $message = 'newsletter.end_date_before_start_date';

    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }
}
