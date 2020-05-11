<?php

declare(strict_types=1);

namespace App\Infrastructure\Validator;

use Attribute;
use Symfony\Component\Validator\Constraint;

#[Attribute]
class ContainsAllowedImageFilename extends Constraint
{
    public string $message = 'image.filename_not_allowed';
}
