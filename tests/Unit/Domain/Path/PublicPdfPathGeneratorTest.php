<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Path;

use App\Domain\Path\PublicPathGenerator;
use App\Domain\Path\PublicPdfPathGenerator;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tests\Builder\LinkBuilder;
use Tests\Builder\NewsletterBuilder;
use Tests\Builder\RecipientBuilder;

class PublicPdfPathGeneratorTest extends TestCase
{
    #[Test]
    public function it_generates_an_absolute_file_path_for_pdf(): void
    {
        $shortName = 'AA';
        $nlDate = '2024-01-01';

        $link = (new LinkBuilder())->build();
        $newsletter = (new NewsletterBuilder())
            ->withRecipient(
                (new RecipientBuilder())
                    ->withShortName($shortName)
                    ->build()
            )
            ->withDate(new DateTimeImmutable($nlDate))
            ->withFirstLink($link)
            ->withLastLink($link)
            ->build();

        $sut = new PublicPdfPathGenerator(new PublicPathGenerator(__DIR__));

        $this->assertMatchesRegularExpression(
            sprintf('#/public/assets/pdfs/%s-%s.pdf#', $shortName, $nlDate),
            $sut->generateAbsoluteFilePath($newsletter)
        );
    }
}
