<?php

declare(strict_types=1);

namespace App\Domain\Entity;

use App\Infrastructure\Repository\NewsletterRepository;
use App\Infrastructure\Validator as NetflinksAssert;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Stringable;

#[NetflinksAssert\ContainsCorrectDateRange]
#[ORM\Entity(repositoryClass: NewsletterRepository::class)]
class Newsletter implements Stringable
{
    #[ORM\Id]
    #[ORM\Column]
    private string $id;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private DateTimeImmutable $date;

    #[ORM\OneToOne(inversedBy: 'newsletter1')]
    #[ORM\JoinColumn(nullable: false)]
    private Link $firstLink;

    #[ORM\OneToOne(inversedBy: 'newsletter2')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Link $lastLink;

    #[ORM\OneToOne(inversedBy: 'newsletter')]
    #[ORM\JoinColumn(nullable: false)]
    private Recipient $recipient;

    #[ORM\ManyToMany(targetEntity: Category::class, inversedBy: 'newsletters')]
    private Collection $categories;

    #[ORM\ManyToMany(targetEntity: Image::class, inversedBy: 'newsletters')]
    private Collection $images;

    public function __construct()
    {
        $this->categories = new ArrayCollection();
        $this->images = new ArrayCollection();
    }

    public function __toString(): string
    {
        return sprintf('%s - %s', $this->recipient->getName(), $this->date->format('Y-m-d'));
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

    public function getDate(): DateTimeImmutable
    {
        return $this->date;
    }

    public function setDate(DateTimeImmutable $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getFirstLink(): Link
    {
        return $this->firstLink;
    }

    public function setFirstLink(Link $firstLink): self
    {
        $this->firstLink = $firstLink;

        return $this;
    }

    public function getLastLink(): Link
    {
        return $this->lastLink;
    }

    public function setLastLink(?Link $lastLink): self
    {
        $this->lastLink = $lastLink;

        return $this;
    }

    public function getRecipient(): Recipient
    {
        return $this->recipient;
    }

    public function setRecipient(Recipient $recipient): self
    {
        $this->recipient = $recipient;

        return $this;
    }

    /**
     * @return Collection<int, Category>
     */
    public function getCategories(): Collection
    {
        return $this->categories;
    }

    public function addCategory(Category $category): self
    {
        if (!$this->categories->contains($category)) {
            $this->categories->add($category);
            $category->addNewsletter($this);
        }

        return $this;
    }

    public function removeCategory(Category $category): self
    {
        if ($this->categories->removeElement($category)) {
            $category->removeNewsletter($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, Image>
     */
    public function getImages(): Collection
    {
        return $this->images;
    }

    public function addImage(Image $images): self
    {
        if (!$this->images->contains($images)) {
            $this->images->add($images);
            $images->addNewsletter($this);
        }

        return $this;
    }

    public function removeImage(Image $images): self
    {
        if ($this->images->removeElement($images)) {
            $images->removeNewsletter($this);
        }

        return $this;
    }
}
