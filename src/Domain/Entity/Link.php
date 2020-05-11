<?php

declare(strict_types=1);

namespace App\Domain\Entity;

use App\Infrastructure\Repository\LinkRepository;
use App\Infrastructure\Validator as NetflinksAssert;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Stringable;
use Symfony\Component\Validator\Constraints as Assert;

#[NetflinksAssert\ContainsAlreadyRegisteredUrl]
#[ORM\Entity(repositoryClass: LinkRepository::class)]
class Link implements Stringable
{
    #[ORM\Id]
    #[ORM\Column]
    private string $id = '';

    #[ORM\Column(length: 255)]
    #[Assert\Length(max: 82)]
    private string $description;

    #[ORM\Column(length: 255)]
    #[Assert\Url]
    private string $url;

    #[ORM\Column]
    private string $note;

    #[ORM\Column]
    private bool $isFavorite;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private DateTimeImmutable $date;

    #[ORM\ManyToOne(inversedBy: 'links')]
    #[ORM\JoinColumn(nullable: false)]
    private Category $category;

    #[ORM\OneToOne(mappedBy: 'firstLink')]
    private Newsletter $newsletter1;

    #[ORM\OneToOne(mappedBy: 'lastLink')]
    private Newsletter $newsletter2;

    #[ORM\ManyToMany(targetEntity: Recipient::class, inversedBy: 'links')]
    private Collection $recipients;

    #[ORM\ManyToMany(targetEntity: Tag::class, inversedBy: 'links')]
    private Collection $tags;

    public function __construct()
    {
        $this->recipients = new ArrayCollection();
        $this->tags = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->description;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function setUrl(string $url): self
    {
        $this->url = $url;

        return $this;
    }

    public function getDate(): DateTimeImmutable
    {
        return $this->date;
    }

    public function setDate(DateTimeImmutable $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getCategory(): Category
    {
        return $this->category;
    }

    public function setCategory(Category $category): self
    {
        $this->category = $category;

        return $this;
    }

    public function getIsFavorite(): bool
    {
        return $this->isFavorite;
    }

    public function setIsFavorite(bool $isFavorite): self
    {
        $this->isFavorite = $isFavorite;

        return $this;
    }

    /**
     * @return Collection<int, Recipient>
     */
    public function getRecipients(): Collection
    {
        return $this->recipients;
    }

    public function addRecipient(Recipient $recipient): self
    {
        if (!$this->recipients->contains($recipient)) {
            $this->recipients->add($recipient);
            $recipient->addLink($this);
        }

        return $this;
    }

    public function removeRecipient(Recipient $recipient): self
    {
        if ($this->recipients->removeElement($recipient)) {
            $recipient->removeLink($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, Tag>
     */
    public function getTags(): Collection
    {
        return $this->tags;
    }

    public function addTag(Tag $tag): self
    {
        if (!$this->tags->contains($tag)) {
            $this->tags->add($tag);
            $tag->addLink($this);
        }

        return $this;
    }

    public function removeTag(Tag $tag): self
    {
        if ($this->tags->removeElement($tag)) {
            $tag->removeLink($this);
        }

        return $this;
    }

    public function getNote(): string
    {
        return $this->note;
    }

    public function setNote(string $note): self
    {
        $this->note = $note;

        return $this;
    }
}
