<?php

declare(strict_types=1);

namespace Tests\Functional\Infrastructure\Repository;

use App\Infrastructure\Repository\NewsletterRepository;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Tests\Builder\NewsletterBuilder;
use Tests\Functional\DummyTrait;

class NewsletterRepositoryTest extends KernelTestCase
{
    use DummyTrait;

    #[Test]
    public function it_adds_then_update_and_remove_a_newsletter(): void
    {
        // Add

        $sut = self::getContainer()->get(NewsletterRepository::class);

        $sut->add(
            $newsletter = (new NewsletterBuilder())
                ->withDate(new DateTimeImmutable('yesterday'))
                ->withFirstLink($link = $this->createDummyLink())
                ->withLastLink($link)
                ->withRecipient($this->createDummyRecipient())
                ->build(),
            flush: true
        );

        $this->assertCount(1, $sut->findAll());

        // Update

        $date = '2000-01-01';
        $newsletter->setDate(new DateTimeImmutable($date));

        $sut->update($newsletter);

        $this->assertSame($date, $sut->findAll()[0]->getDate()->format('Y-m-d'));

        // Remove

        $sut->remove($newsletter, flush: true);

        $this->assertCount(0, $sut->findAll());
    }
}
