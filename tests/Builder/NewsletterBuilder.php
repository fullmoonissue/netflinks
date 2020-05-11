<?php

declare(strict_types=1);

namespace Tests\Builder;

use App\Domain\Entity\Category;
use App\Domain\Entity\Image;
use App\Domain\Entity\Link;
use App\Domain\Entity\Newsletter;
use App\Domain\Entity\Recipient;
use DateTimeImmutable;
use Ramsey\Uuid\Uuid;

class NewsletterBuilder
{
    private Link $firstLink;

    private ?Link $lastLink = null;

    private ?Recipient $recipient = null;

    private ?DateTimeImmutable $date = null;

    /**
     * @var Category[]
     */
    private array $categories = [];

    /**
     * @var Image[]
     */
    private array $images = [];

    public function withFirstLink(Link $link): self
    {
        $this->firstLink = $link;

        return $this;
    }

    public function withLastLink(Link $link): self
    {
        $this->lastLink = $link;

        return $this;
    }

    /**
     * @param Category[] $categories
     */
    public function withCategories(array $categories): self
    {
        $this->categories = $categories;

        return $this;
    }

    public function withDate(DateTimeImmutable $date): self
    {
        $this->date = $date;

        return $this;
    }

    /**
     * @param Image[] $images
     */
    public function withImages(array $images): self
    {
        $this->images = $images;

        return $this;
    }

    public function withRecipient(Recipient $recipient): self
    {
        $this->recipient = $recipient;

        return $this;
    }

    public function build(): Newsletter
    {
        $newsletter = (new Newsletter())
            ->setId(Uuid::uuid4()->toString())
            ->setFirstLink($this->firstLink)
            ->setLastLink($this->lastLink);

        if ($this->recipient instanceof Recipient) {
            $newsletter->setRecipient($this->recipient);
        }

        if ($this->date instanceof DateTimeImmutable) {
            $newsletter->setDate($this->date);
        }

        foreach ($this->categories as $category) {
            $newsletter->addCategory($category);
        }

        foreach ($this->images as $image) {
            $newsletter->addImage($image);
        }

        return $newsletter;
    }
}
