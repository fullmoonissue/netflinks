<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Image;

use App\Domain\Image\AllowedImageExtension;
use App\Domain\Image\IsImageExtensionEligible;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tests\Builder\ImageBuilder;

class IsImageExtensionEligibleTest extends TestCase
{
    #[Test]
    #[DataProvider('getAllowedExtensions')]
    public function it_is_satisfied_by_eligible_extensions(string $allowedImageExtension): void
    {
        $image = (new ImageBuilder())
            ->withFilename(sprintf('filename.%s', $allowedImageExtension))
            ->build();

        $sut = new IsImageExtensionEligible();
        $this->assertTrue($sut->isSatisfiedBy($image));
    }

    public static function getAllowedExtensions(): iterable
    {
        foreach (AllowedImageExtension::values() as $allowedImageExtension) {
            yield [$allowedImageExtension];
        }
    }

    #[Test]
    public function it_is_not_satisfied_by_not_eligible_extension(): void
    {
        $extension = 'mp3';

        $this->assertNotContains($extension, AllowedImageExtension::values());

        $image = (new ImageBuilder())
            ->withFilename(sprintf('filename.%s', $extension))
            ->build();

        $sut = new IsImageExtensionEligible();
        $this->assertFalse($sut->isSatisfiedBy($image));
    }
}
