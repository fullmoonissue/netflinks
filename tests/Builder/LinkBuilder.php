<?php

declare(strict_types=1);

namespace Tests\Builder;

use App\Domain\Entity\Category;
use App\Domain\Entity\Link;
use App\Domain\Entity\Recipient;
use DateTimeImmutable;
use Ramsey\Uuid\Uuid;

class LinkBuilder
{
    private ?Category $category = null;

    private ?DateTimeImmutable $date = null;

    /**
     * @var Recipient[]
     */
    private array $recipients = [];

    private string $description = 'whatever';

    private string $url = 'whatever';

    public function withCategory(Category $category): self
    {
        $this->category = $category;

        return $this;
    }

    public function withDate(DateTimeImmutable $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function withDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @param Recipient[] $recipients
     */
    public function withRecipients(array $recipients): self
    {
        $this->recipients = $recipients;

        return $this;
    }

    public function withUrl(string $url): self
    {
        $this->url = $url;

        return $this;
    }

    public function build(): Link
    {
        $link = (new Link())
            ->setId(Uuid::uuid4()->toString())
            ->setDate($this->date ?? new DateTimeImmutable())
            ->setDescription($this->description)
            ->setUrl($this->url);

        if ($this->category instanceof Category) {
            $link->setCategory($this->category);
        }

        foreach ($this->recipients as $recipient) {
            $link->addRecipient($recipient);
        }

        return $link;
    }
}
