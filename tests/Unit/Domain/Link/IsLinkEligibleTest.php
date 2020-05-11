<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Link;

use App\Domain\Link\IsLinkEligible;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tests\Builder\CategoryBuilder;
use Tests\Builder\LinkBuilder;
use Tests\Builder\NewsletterBuilder;
use Tests\Builder\RecipientBuilder;

class IsLinkEligibleTest extends TestCase
{
    #[Test]
    public function it_is_satisfied_when_category_is_attached_to_recipient_and_recipient_is_attached_to_nl(): void
    {
        $recipient = (new RecipientBuilder())->build();

        $category = (new CategoryBuilder())->build();

        $link = (new LinkBuilder())
            ->withRecipients([$recipient])
            ->withCategory($category)
            ->build();

        $newsletter = (new NewsletterBuilder())
            ->withFirstLink($link)
            ->withLastLink($link)
            ->withRecipient($recipient)
            ->withCategories([$category])
            ->build();

        $sut = new IsLinkEligible();
        $this->assertTrue($sut->isSatisfiedBy($link, $newsletter));
    }

    #[Test]
    public function it_does_not_satisfied_when_recipient_is_not_attached_to(): void
    {
        $recipientA = (new RecipientBuilder())
            ->withShortName('A')
            ->build();

        $link = (new LinkBuilder())
            ->withRecipients([$recipientA])
            ->build();

        $recipientB = (new RecipientBuilder())
            ->withShortName('B')
            ->build();

        $newsletter = (new NewsletterBuilder())
            ->withFirstLink($link)
            ->withLastLink($link)
            ->withRecipient($recipientB)
            ->build();

        $sut = new IsLinkEligible();
        $this->assertFalse($sut->isSatisfiedBy($link, $newsletter));
    }

    #[Test]
    public function it_does_not_satisfied_when_category_is_not_attached_to_recipient(): void
    {
        $recipient = (new RecipientBuilder())->build();

        $category = (new CategoryBuilder())->build();

        $link = (new LinkBuilder())
            ->withRecipients([$recipient])
            ->withCategory($category)
            ->build();

        $otherCategory = (new CategoryBuilder())->build();

        $newsletter = (new NewsletterBuilder())
            ->withFirstLink($link)
            ->withLastLink($link)
            ->withRecipient($recipient)
            ->withCategories([$otherCategory])
            ->build();

        $sut = new IsLinkEligible();
        $this->assertFalse($sut->isSatisfiedBy($link, $newsletter));
    }
}
