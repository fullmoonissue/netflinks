<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Image;

use App\Domain\Image\UnregisteredImageExtractor;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tests\Builder\ImageBuilder;
use Tests\Double\Fake\Infrastructure\Repository\ImageRepositoryFake;
use Tests\Double\Stub\Application\Path\PublicPathGeneratorStub;

class UnregisteredImageExtractorTest extends TestCase
{
    #[Test]
    public function it_extracts_unregistered_images(): void
    {
        $alreadyExistingImage = (new ImageBuilder())
            ->withFilename('already_existant_image.jpg')
            ->build();

        $sut = new UnregisteredImageExtractor(
            new PublicPathGeneratorStub(__DIR__.'/images'),
            new ImageRepositoryFake([$alreadyExistingImage])
        );

        $this->assertSame(
            ['0bfafebd50d7705cbd2a0bf6a1e92782.jpeg'],
            $sut->extract() // Skippable files skipped + already existant image skipped
        );
    }
}
