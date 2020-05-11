<?php

declare(strict_types=1);

namespace App\Infrastructure\Validator;

use App\Domain\Entity\Image;
use App\Domain\Image\AllowedImageExtension;
use App\Domain\Image\IsImageExtensionEligibleInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class ContainsAllowedImageFilenameValidator extends ConstraintValidator
{
    public function __construct(
        private readonly IsImageExtensionEligibleInterface $isImageExtensionEligible,
    ) {
    }

    public function validate(mixed $value, Constraint $constraint): void
    {
        /** @var string $filename */
        $filename = $value;

        /** @var ContainsAllowedImageFilename $containsAllowedImageFilenameExtensionConstraint */
        $containsAllowedImageFilenameExtensionConstraint = $constraint;

        $imageTemp = (new Image())->setFilename($filename);
        if (!$this->isImageExtensionEligible->isSatisfiedBy($imageTemp)) {
            $this
                ->context
                ->buildViolation($containsAllowedImageFilenameExtensionConstraint->message)
                ->setParameter('{{ forbidden_extension }}', $filename)
                ->setParameter('{{ allowed_extensions }}', implode(', ', AllowedImageExtension::values()))
                ->addViolation();
        }
    }
}
