<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Newsletter;

use App\Domain\Newsletter\AreImagesAssignedToRecipient;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tests\Builder\ImageBuilder;
use Tests\Builder\LinkBuilder;
use Tests\Builder\NewsletterBuilder;
use Tests\Builder\RecipientBuilder;

class AreImagesAssignedToRecipientTest extends TestCase
{
    #[Test]
    public function it_is_satisfied_when_all_images_are_assigned_to_recipient(): void
    {
        $recipient = (new RecipientBuilder())->build();

        $image1 = (new ImageBuilder())
            ->withRecipients([$recipient])
            ->build();

        $image2 = (new ImageBuilder())
            ->withRecipients([$recipient])
            ->build();

        $link = (new LinkBuilder())->build();
        $newsletter = (new NewsletterBuilder())
            ->withImages([$image1, $image2])
            ->withRecipient($recipient)
            ->withFirstLink($link)
            ->withLastLink($link)
            ->build();

        $sut = new AreImagesAssignedToRecipient();
        $this->assertTrue($sut->isSatisfiedBy($newsletter));
    }

    #[Test]
    public function it_does_not_satisfied_when_one_image_is_not_assigned_to_recipient(): void
    {
        $recipient = (new RecipientBuilder())->build();

        $image1 = (new ImageBuilder())
            ->withRecipients([$recipient])
            ->build();

        $image2 = (new ImageBuilder())->build(); // <-- no recipient

        $link = (new LinkBuilder())->build();
        $newsletter = (new NewsletterBuilder())
            ->withImages([$image1, $image2])
            ->withRecipient($recipient)
            ->withFirstLink($link)
            ->withLastLink($link)
            ->build();

        $sut = new AreImagesAssignedToRecipient();
        $this->assertFalse($sut->isSatisfiedBy($newsletter));
    }
}
