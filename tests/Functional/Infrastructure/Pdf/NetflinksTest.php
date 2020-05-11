<?php

declare(strict_types=1);

namespace Tests\Functional\Infrastructure\Pdf;

use App\Infrastructure\Pdf\Netflinks;
use App\Infrastructure\Repository\CategoryRepository;
use App\Infrastructure\Repository\ImageRepository;
use App\Infrastructure\Repository\LinkRepository;
use App\Infrastructure\Repository\NewsletterRepository;
use App\Infrastructure\Repository\RecipientRepository;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Tests\Builder\CategoryBuilder;
use Tests\Builder\ImageBuilder;
use Tests\Builder\LinkBuilder;
use Tests\Builder\NewsletterBuilder;
use Tests\Builder\RecipientBuilder;
use Throwable;

class NetflinksTest extends KernelTestCase
{
    #[Test]
    public function it_prepares_the_pdf(): void
    {
        self::getContainer()->get(CategoryRepository::class)->add(
            $category = (new CategoryBuilder())->build(),
            true
        );
        self::getContainer()->get(RecipientRepository::class)->add(
            $recipient = (new RecipientBuilder())->build(),
            true
        );
        self::getContainer()->get(LinkRepository::class)->add(
            $link = (new LinkBuilder())
                ->withCategory($category)
                ->withRecipients([$recipient])
                ->build(),
            true
        );
        self::getContainer()->get(ImageRepository::class)->add(
            $image = (new ImageBuilder())
                ->withRecipients([$recipient])
                ->build(),
            true
        );
        self::getContainer()->get(NewsletterRepository::class)->add(
            $newsletter = (new NewsletterBuilder())
                ->withFirstLink($link)
                ->withLastLink($link)
                ->withRecipient($recipient)
                ->withDate(new DateTimeImmutable())
                ->withCategories([$category])
                ->withImages([$image])
                ->build(),
            true
        );

        /** @var Netflinks $sut */
        $sut = self::getContainer()->get(Netflinks::class);

        $noExceptionThrown = true;
        try {
            $sut->prepareDocument($newsletter);
        } catch (Throwable) {
            $noExceptionThrown = false;
        }

        $this->assertTrue($noExceptionThrown);
    }

    #[Test]
    public function it_throws_an_exception_when_not_exact_count_of_images_are_linked_to_the_nl(): void
    {
        self::getContainer()->get(CategoryRepository::class)->add(
            $category = (new CategoryBuilder())->build(),
            true
        );
        self::getContainer()->get(RecipientRepository::class)->add(
            $recipient = (new RecipientBuilder())->build(),
            true
        );
        self::getContainer()->get(LinkRepository::class)->add(
            $link = (new LinkBuilder())
                ->withCategory($category)
                ->withRecipients([$recipient])
                ->build(),
            true
        );
        self::getContainer()->get(NewsletterRepository::class)->add(
            $newsletter = (new NewsletterBuilder())
                ->withFirstLink($link)
                ->withLastLink($link)
                ->withRecipient($recipient)
                ->withDate(new DateTimeImmutable())
                ->withCategories([$category])
                ->build(),
            true
        );

        $this->expectExceptionMessage('Exactement 1 image(s) requise(s) (0 assignée(s))');

        /** @var Netflinks $sut */
        $sut = self::getContainer()->get(Netflinks::class);
        $sut->prepareDocument($newsletter);
    }

    #[Test]
    public function it_throws_an_exception_when_at_least_one_nl_image_is_not_assigned_to_recipient(): void
    {
        self::getContainer()->get(CategoryRepository::class)->add(
            $category = (new CategoryBuilder())->build(),
            true
        );
        self::getContainer()->get(RecipientRepository::class)->add(
            $recipient = (new RecipientBuilder())->build(),
            true
        );
        self::getContainer()->get(LinkRepository::class)->add(
            $link = (new LinkBuilder())
                ->withCategory($category)
                ->withRecipients([$recipient])
                ->build(),
            true
        );
        self::getContainer()->get(ImageRepository::class)->add(
            $image = (new ImageBuilder())->build(),
            true
        );
        self::getContainer()->get(NewsletterRepository::class)->add(
            $newsletter = (new NewsletterBuilder())
                ->withFirstLink($link)
                ->withLastLink($link)
                ->withRecipient($recipient)
                ->withDate(new DateTimeImmutable())
                ->withCategories([$category])
                ->withImages([$image])
                ->build(),
            true
        );

        $this->expectExceptionMessage('Au moins une image n\'est pas assignée avec le destinataire');

        /** @var Netflinks $sut */
        $sut = self::getContainer()->get(Netflinks::class);
        $sut->prepareDocument($newsletter);
    }
}
