<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Newsletter;

use App\Domain\Newsletter\RequestedImagesCounter;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tests\Builder\CategoryBuilder;
use Tests\Builder\LinkBuilder;
use Tests\Builder\NewsletterBuilder;
use Tests\Double\Stub\Domain\Link\LinksExtractorStub;

class RequestedImagesCounterTest extends TestCase
{
    #[Test]
    public function it_counts_one_requested_images(): void
    {
        $link1 = (new LinkBuilder())
            ->withCategory(
                $category = (new CategoryBuilder())->build()
            )
            ->build();
        $link2 = (new LinkBuilder())
            ->withCategory($category)
            ->build();

        $newsletter = (new NewsletterBuilder())
            ->withFirstLink($link1)
            ->withLastLink($link2)
            ->build();

        $sut = new RequestedImagesCounter(
            new LinksExtractorStub([$link1, $link2])
        );
        $this->assertSame(1, $sut->count($newsletter));
    }

    #[Test]
    public function it_counts_multiple_requested_images(): void
    {
        $link1 = (new LinkBuilder())
            ->withCategory(
                (new CategoryBuilder())->build()
            )
            ->build();
        $link2 = (new LinkBuilder())
            ->withCategory(
                (new CategoryBuilder())->build()
            )
            ->build();

        $newsletter = (new NewsletterBuilder())
            ->withFirstLink($link1)
            ->withLastLink($link2)
            ->build();

        $sut = new RequestedImagesCounter(
            new LinksExtractorStub([$link1, $link2])
        );
        $this->assertSame(2, $sut->count($newsletter));
    }
}
