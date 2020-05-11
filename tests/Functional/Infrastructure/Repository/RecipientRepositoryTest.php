<?php

declare(strict_types=1);

namespace Tests\Functional\Infrastructure\Repository;

use App\Infrastructure\Repository\NewsletterRepository;
use App\Infrastructure\Repository\RecipientRepository;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Tests\Builder\NewsletterBuilder;
use Tests\Builder\RecipientBuilder;
use Tests\Functional\DummyTrait;

class RecipientRepositoryTest extends KernelTestCase
{
    use DummyTrait;

    #[Test]
    public function it_adds_and_remove_a_recipient(): void
    {
        $sut = self::getContainer()->get(RecipientRepository::class);

        $sut->add(
            $recipient = (new RecipientBuilder())->build(),
            flush: true
        );

        $this->assertCount(1, $sut->findAll());

        $sut->remove($recipient, flush: true);

        $this->assertCount(0, $sut->findAll());
    }

    #[Test]
    public function it_sorts_for_association_field(): void
    {
        $sut = self::getContainer()->get(RecipientRepository::class);

        $zRecipient = 'z';
        $sut->add(
            (new RecipientBuilder())
                ->withName($zRecipient)
                ->build(),
            flush: true
        );

        $bRecipient = 'b';
        $sut->add(
            (new RecipientBuilder())
                ->withName($bRecipient)
                ->build(),
            flush: true
        );

        $recipients = $sut->orderedForAssociationField()->getQuery()->getResult();
        $this->assertSame($bRecipient, $recipients[0]->getName());
        $this->assertSame($zRecipient, $recipients[1]->getName());
    }

    #[Test]
    public function it_filters_and_sorts_for_association_field(): void
    {
        $sut = self::getContainer()->get(RecipientRepository::class);

        $zRecipientName = 'z';
        $sut->add(
            (new RecipientBuilder())
                ->withName($zRecipientName)
                ->build(),
            flush: true
        );

        $bRecipientName = 'b';
        $sut->add(
            $bRecipient = (new RecipientBuilder())
                ->withName($bRecipientName)
                ->build(),
            flush: true
        );

        $recipients = $sut->filteredAndOrderedForAssociationField()->getQuery()->getResult();
        $this->assertSame($bRecipientName, $recipients[0]->getName());
        $this->assertSame($zRecipientName, $recipients[1]->getName());

        self::getContainer()->get(NewsletterRepository::class)->add(
            (new NewsletterBuilder())
                ->withDate(new DateTimeImmutable('yesterday'))
                ->withFirstLink($link = $this->createDummyLink())
                ->withLastLink($link)
                ->withRecipient($bRecipient)
                ->build(),
            flush: true
        );

        $recipients = $sut->filteredAndOrderedForAssociationField()->getQuery()->getResult();
        $this->assertCount(1, $recipients); // <-- no more 2 recipients
    }
}
