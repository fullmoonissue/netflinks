<?php

declare(strict_types=1);

namespace App\Infrastructure\Validator;

use Attribute;
use Symfony\Component\Validator\Constraint;

#[Attribute]
class ContainsAlreadyRegisteredUrl extends Constraint
{
    public string $message = 'link.url_already_registered';

    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }
}
