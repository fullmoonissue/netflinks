<?php

declare(strict_types=1);

namespace App\Infrastructure\Validator;

use App\Domain\Entity\Newsletter;
use App\Domain\Newsletter\LastLinkGuesserInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class ContainsCorrectDateRangeValidator extends ConstraintValidator
{
    public function __construct(
        private readonly LastLinkGuesserInterface $lastLinkGuesser,
    ) {
    }

    public function validate(mixed $value, Constraint $constraint): void
    {
        /** @var Newsletter $newsletter */
        $newsletter = $value;

        /** @var ContainsCorrectDateRange $containsCorrectDateRangeExtensionConstraint */
        $containsCorrectDateRangeExtensionConstraint = $constraint;

        $lastLink = $this->lastLinkGuesser->guess($newsletter);

        if ($lastLink->getDate() < $newsletter->getFirstLink()->getDate()) {
            $this
                ->context
                ->buildViolation($containsCorrectDateRangeExtensionConstraint->message)
                ->addViolation();
        }
    }
}
