<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Validator;

use App\Domain\Image\AllowedImageExtension;
use App\Infrastructure\Validator\ContainsAllowedImageFilename;
use App\Infrastructure\Validator\ContainsAllowedImageFilenameValidator;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;
use Tests\Double\Stub\Domain\Image\IsImageExtensionEligibleStub;

class ContainsAllowedImageFilenameValidatorTest extends ConstraintValidatorTestCase
{
    // This is a horrible hack.
    // The value in IsImageExtensionEligibleStub() inside the createValidator() can't be set easily.
    // So the workaround is to use this static bool by switching from true to false and vice versa.
    // The order of the tests can't be changed and no DataProvider can be used (or at your own risk).
    private static bool $isImageExtensionEligible = true;

    #[Test]
    public function it_validates_that_it_contains_allowed_image_filename_extensions(): void
    {
        $extension = AllowedImageExtension::values()[0];

        $this->validator->validate(
            sprintf('filename.%s', $extension),
            new ContainsAllowedImageFilename()
        );

        $this->assertNoViolation();
    }

    #[Test]
    public function it_validates_that_it_does_not_contain_allowed_image_filename_extension(): void
    {
        $extension = 'mp3';

        $this->assertNotContains($extension, AllowedImageExtension::values());

        $this->validator->validate(
            $filename = sprintf('filename.%s', $extension),
            new ContainsAllowedImageFilename()
        );

        $this
            ->buildViolation('image.filename_not_allowed')
            ->setParameter('{{ forbidden_extension }}', $filename)
            ->setParameter('{{ allowed_extensions }}', implode(', ', AllowedImageExtension::values()))
            ->assertRaised();
    }

    protected function createValidator(): ConstraintValidatorInterface
    {
        $isImageExtensionEligible = self::$isImageExtensionEligible;
        self::$isImageExtensionEligible = !self::$isImageExtensionEligible;

        return new ContainsAllowedImageFilenameValidator(
            new IsImageExtensionEligibleStub($isImageExtensionEligible)
        );
    }
}
