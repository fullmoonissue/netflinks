<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Path;

use App\Domain\Path\PublicImagePathGenerator;
use App\Domain\Path\PublicPathGenerator;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tests\Builder\ImageBuilder;

class PublicImagePathGeneratorTest extends TestCase
{
    #[Test]
    public function it_generates_an_absolute_file_path_for_image(): void
    {
        $filename = 'image.jpeg';

        $image = (new ImageBuilder())
            ->withFilename($filename)
            ->build();

        $sut = new PublicImagePathGenerator(new PublicPathGenerator(__DIR__));

        $this->assertMatchesRegularExpression(
            '#/public/assets/images#',
            $sut->generateAbsoluteFolderPath()
        );

        $this->assertMatchesRegularExpression(
            sprintf('#/public/assets/images/%s#', $filename),
            $sut->generateAbsoluteFilePath($image)
        );
    }
}
