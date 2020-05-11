<?php

declare(strict_types=1);

namespace Tests\Functional\Domain\Newsletter;

use App\Domain\Newsletter\AssignImagesToRecipient;
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

class AssignImagesToRecipientTest extends KernelTestCase
{
    #[Test]
    public function it_assignes_images_to_recipient(): void
    {
        self::getContainer()->get(CategoryRepository::class)->add(
            $category = (new CategoryBuilder())->build(),
            true
        );
        self::getContainer()->get(LinkRepository::class)->add(
            $link = (new LinkBuilder())
                ->withCategory($category)
                ->build(),
            true
        );
        self::getContainer()->get(RecipientRepository::class)->add(
            $recipient = (new RecipientBuilder())->build(),
            true
        );
        $imageRepository = self::getContainer()->get(ImageRepository::class);
        $imageRepository->add(
            $image = (new ImageBuilder())->build(),
            true
        );
        self::getContainer()->get(NewsletterRepository::class)->add(
            $newsletter = (new NewsletterBuilder())
                ->withFirstLink($link)
                ->withLastLink($link)
                ->withRecipient($recipient)
                ->withImages([$image])
                ->withDate(new DateTimeImmutable())
                ->build(),
            true
        );

        $this->assertCount(0, $imageRepository->find($image->getId())->getRecipients());

        $sut = self::getContainer()->get(AssignImagesToRecipient::class);
        $sut->assign($newsletter);

        $this->assertCount(1, $imageRepository->find($image->getId())->getRecipients());
    }

    #[Test]
    public function it_does_not_assign_image_to_recipient_when_already_assigned(): void
    {
        self::getContainer()->get(CategoryRepository::class)->add(
            $category = (new CategoryBuilder())->build(),
            true
        );
        self::getContainer()->get(LinkRepository::class)->add(
            $link = (new LinkBuilder())
                ->withCategory($category)
                ->build(),
            true
        );
        self::getContainer()->get(RecipientRepository::class)->add(
            $recipient = (new RecipientBuilder())->build(),
            true
        );
        $imageRepository = self::getContainer()->get(ImageRepository::class);
        $imageRepository->add(
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
                ->withImages([$image])
                ->withDate(new DateTimeImmutable())
                ->build(),
            true
        );

        $this->assertCount(1, $imageRepository->find($image->getId())->getRecipients());

        $sut = self::getContainer()->get(AssignImagesToRecipient::class);
        $sut->assign($newsletter);

        $this->assertCount(1, $imageRepository->find($image->getId())->getRecipients());
    }
}
