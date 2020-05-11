<?php

declare(strict_types=1);

namespace Tests\Functional\Domain\Newsletter;

use App\Domain\Newsletter\LastLinkGuesser;
use App\Infrastructure\Repository\CategoryRepository;
use App\Infrastructure\Repository\LinkRepository;
use App\Infrastructure\Repository\NewsletterRepository;
use App\Infrastructure\Repository\RecipientRepository;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Tests\Builder\CategoryBuilder;
use Tests\Builder\LinkBuilder;
use Tests\Builder\NewsletterBuilder;
use Tests\Builder\RecipientBuilder;

class LastLinkGuesserTest extends KernelTestCase
{
    #[Test]
    public function it_guesses_the_last_link_from_nl(): void
    {
        self::getContainer()->get(CategoryRepository::class)->add(
            $category = (new CategoryBuilder())->build(),
            true
        );

        $linkRepository = self::getContainer()->get(LinkRepository::class);
        $linkRepository->add(
            $link1 = (new LinkBuilder())
                ->withCategory($category)
                ->withDate(new DateTimeImmutable('4 days ago'))
                ->build(),
            true
        );
        $linkRepository->add(
            $link2 = (new LinkBuilder())
                ->withCategory($category)
                ->withDate(new DateTimeImmutable('3 days ago'))
                ->build(),
            true
        );

        self::getContainer()->get(RecipientRepository::class)->add(
            $recipient = (new RecipientBuilder())->build(),
            true
        );
        $newsletterRepository = self::getContainer()->get(NewsletterRepository::class);
        $newsletterRepository->add(
            $newsletter = (new NewsletterBuilder())
                ->withFirstLink($link1)
                ->withLastLink($link2)
                ->withRecipient($recipient)
                ->withDate(new DateTimeImmutable())
                ->build(),
            true
        );

        $sut = self::getContainer()->get(LastLinkGuesser::class);
        $guessedLink = $sut->guess($newsletter);

        $this->assertSame(
            $link2->getId(),
            $guessedLink->getId()
        );
    }

    #[Test]
    public function it_guesses_the_last_link_from_link_repository(): void
    {
        self::getContainer()->get(CategoryRepository::class)->add(
            $category = (new CategoryBuilder())->build(),
            true
        );

        $linkRepository = self::getContainer()->get(LinkRepository::class);
        $linkRepository->add(
            $link1 = (new LinkBuilder())
                ->withCategory($category)
                ->withDate(new DateTimeImmutable('4 days ago'))
                ->build(),
            true
        );
        $linkRepository->add(
            $link2 = (new LinkBuilder())
                ->withCategory($category)
                ->withDate(new DateTimeImmutable('3 days ago'))
                ->build(),
            true
        );

        self::getContainer()->get(RecipientRepository::class)->add(
            $recipient = (new RecipientBuilder())->build(),
            true
        );

        $newsletter = (new NewsletterBuilder())
            ->withFirstLink($link1) // <-- no last link
            ->withRecipient($recipient)
            ->withDate(new DateTimeImmutable())
            ->build();

        $sut = self::getContainer()->get(LastLinkGuesser::class);
        $guessedLink = $sut->guess($newsletter);

        $this->assertSame(
            $link2->getId(),
            $guessedLink->getId()
        );
    }
}
