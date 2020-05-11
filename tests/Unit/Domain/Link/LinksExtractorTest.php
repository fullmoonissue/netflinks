<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Link;

use App\Domain\Entity\Link;
use App\Domain\Link\LinksExtractor;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tests\Builder\LinkBuilder;
use Tests\Builder\NewsletterBuilder;
use Tests\Double\Fake\Infrastructure\Repository\LinkRepositoryFake;
use Tests\Double\Stub\Domain\Link\IsLinkEligibleStub;

class LinksExtractorTest extends TestCase
{
    #[Test]
    public function it_extracts_links(): void
    {
        $links = $this->getLinks();

        $sut = $this->createLinksExtractor(array_values($links), isLinkEligible: true);

        $newsletter = (new NewsletterBuilder())
            ->withFirstLink($links['yesterday'])
            ->withLastLink($links['now'])
            ->build();

        $links = $sut->extract($newsletter);

        $this->assertCount(3, $links);
    }

    #[Test]
    public function it_extracts_no_link_because_none_is_eligible(): void
    {
        $links = $this->getLinks();

        $sut = $this->createLinksExtractor(array_values($links), isLinkEligible: false);

        $newsletter = (new NewsletterBuilder())
            ->withFirstLink($links['- 2 days'])
            ->withLastLink($links['now'])
            ->build();

        $links = $sut->extract($newsletter);

        $this->assertCount(0, $links);
    }

    /**
     * @return array<string, Link>
     */
    private function getLinks(): array
    {
        return [
            '- 5 hours' => (new LinkBuilder())->withDate(new DateTimeImmutable('- 5 hours'))->build(),
            '- 2 days' => (new LinkBuilder())->withDate(new DateTimeImmutable('- 2 days'))->build(),
            'now' => (new LinkBuilder())->withDate(new DateTimeImmutable('now'))->build(),
            'yesterday' => (new LinkBuilder())->withDate(new DateTimeImmutable('yesterday'))->build(),
        ];
    }

    /**
     * @param array<int, Link> $links
     */
    private function createLinksExtractor(array $links, bool $isLinkEligible): LinksExtractor
    {
        return new LinksExtractor(
            new LinkRepositoryFake($links),
            new IsLinkEligibleStub($isLinkEligible)
        );
    }
}
