<?php

declare(strict_types=1);

namespace Tests\Functional\Infrastructure\Repository;

use App\Infrastructure\Repository\LinkRepository;
use DateTimeImmutable;
use Doctrine\ORM\NoResultException;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Tests\Builder\LinkBuilder;
use Tests\Functional\DummyTrait;

class LinkRepositoryTest extends KernelTestCase
{
    use DummyTrait;

    #[Test]
    public function it_adds_and_remove_a_link(): void
    {
        $category = $this->createDummyCategory();

        $sut = self::getContainer()->get(LinkRepository::class);

        $sut->add(
            $link = (new LinkBuilder())
                ->withCategory($category)
                ->build(),
            flush: true
        );

        $this->assertCount(1, $sut->findAll());

        $sut->remove($link, flush: true);

        $this->assertCount(0, $sut->findAll());
    }

    #[Test]
    public function it_returns_next_link(): void
    {
        $category = $this->createDummyCategory();

        $sut = self::getContainer()->get(LinkRepository::class);

        $dateRanges = ['- 3 days', '- 2 days', 'yesterday', 'now'];
        foreach ($dateRanges as $dateRange) {
            $sut->add(
                (new LinkBuilder())
                    ->withCategory($category)
                    ->withDate(new DateTimeImmutable($dateRange))
                    ->withDescription($dateRange)
                    ->build(),
                flush: true
            );
        }

        $link = $sut->getNextLink(new DateTimeImmutable('- 2 hours'));

        $this->assertSame('now', $link->getDescription());
    }

    #[Test]
    public function it_throws_an_exception_because_no_next_link_was_found(): void
    {
        $category = $this->createDummyCategory();

        $sut = self::getContainer()->get(LinkRepository::class);

        $sut->add(
            (new LinkBuilder())
                ->withCategory($category)
                ->withDate(new DateTimeImmutable('- 3 days'))
                ->build(),
            flush: true
        );

        $this->expectException(NoResultException::class);

        $sut->getNextLink(new DateTimeImmutable('- 2 hours'));
    }

    #[Test]
    public function it_returns_last_link(): void
    {
        $category = $this->createDummyCategory();

        $sut = self::getContainer()->get(LinkRepository::class);

        $dateRanges = ['- 3 days', 'now', '- 2 days', 'yesterday'];
        foreach ($dateRanges as $dateRange) {
            $sut->add(
                (new LinkBuilder())
                    ->withCategory($category)
                    ->withDate(new DateTimeImmutable($dateRange))
                    ->withDescription($dateRange)
                    ->build(),
                flush: true
            );
        }

        $link = $sut->getLastLink();

        $this->assertSame('now', $link->getDescription());
    }

    #[Test]
    public function it_returns_ranged_links(): void
    {
        $category = $this->createDummyCategory();

        $sut = self::getContainer()->get(LinkRepository::class);

        $dateRanges = ['- 4 days', '- 2 days', 'yesterday'];
        foreach ($dateRanges as $dateRange) {
            $sut->add(
                (new LinkBuilder())
                    ->withCategory($category)
                    ->withDate(new DateTimeImmutable($dateRange))
                    ->build(),
                flush: true
            );
        }

        $links = $sut->getRangedLinks(new DateTimeImmutable('- 3 days'), new DateTimeImmutable('now'));

        $this->assertCount(2, $links);
    }

    #[Test]
    public function it_detects_an_already_registered_url(): void
    {
        $category = $this->createDummyCategory();

        $sut = self::getContainer()->get(LinkRepository::class);

        $url = 'https://www.perdu.com';
        $sut->add(
            (new LinkBuilder())
                ->withCategory($category)
                ->withUrl($url)
                ->build(),
            flush: true
        );

        $this->assertTrue($sut->isUrlAlreadyRegistered(sprintf('%s/', $url)));
    }
}
