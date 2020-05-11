<?php

declare(strict_types=1);

namespace App\Infrastructure\Validator;

use App\Domain\Entity\Link;
use App\Domain\Repository\LinkRepositoryInterface;
use Error;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class ContainsAlreadyRegisteredUrlValidator extends ConstraintValidator
{
    public function __construct(
        private readonly LinkRepositoryInterface $linkRepository,
    ) {
    }

    public function validate(mixed $value, Constraint $constraint): void
    {
        /** @var Link $link */
        $link = $value;

        try {
            $isUpdateOperation = !empty($link->getId());
        } catch (Error) {
            $isUpdateOperation = false;
        }

        /** @var ContainsAlreadyRegisteredUrl $containsAlreadyRegisteredUrl */
        $containsAlreadyRegisteredUrl = $constraint;

        if (!$isUpdateOperation && $this->linkRepository->isUrlAlreadyRegistered($link->getUrl())) {
            $this
                ->context
                ->buildViolation($containsAlreadyRegisteredUrl->message)
                ->addViolation();
        }
    }
}
