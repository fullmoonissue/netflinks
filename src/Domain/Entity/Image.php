<?php

declare(strict_types=1);

namespace App\Domain\Entity;

use App\Infrastructure\Repository\ImageRepository;
use App\Infrastructure\Validator as NetflinksAssert;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Stringable;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ImageRepository::class)]
class Image implements Stringable
{
    #[ORM\Id]
    #[ORM\Column]
    private string $id;

    #[NetflinksAssert\ContainsAllowedImageFilename]
    #[ORM\Column(length: 255)]
    #[Assert\Regex(pattern: "/^[a-fA-F0-9]{32}\.[a-z]{3,4}$/")]
    private string $filename;

    #[ORM\ManyToMany(targetEntity: Recipient::class, inversedBy: 'images')]
    private Collection $recipients;

    #[ORM\ManyToMany(targetEntity: Newsletter::class, mappedBy: 'images')]
    private Collection $newsletters;

    public function __construct()
    {
        $this->recipients = new ArrayCollection();
        $this->newsletters = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->filename;
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

    public function getFilename(): string
    {
        return $this->filename;
    }

    public function setFilename(string $filename): self
    {
        $this->filename = $filename;

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
            $recipient->addImage($this);
        }

        return $this;
    }

    public function removeRecipient(Recipient $recipient): self
    {
        if ($this->recipients->removeElement($recipient)) {
            $recipient->removeImage($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, Newsletter>
     */
    public function getNewsletters(): Collection
    {
        return $this->newsletters;
    }

    public function addNewsletter(Newsletter $newsletter): self
    {
        if (!$this->newsletters->contains($newsletter)) {
            $this->newsletters->add($newsletter);
            $newsletter->addImage($this);
        }

        return $this;
    }

    public function removeNewsletter(Newsletter $newsletter): self
    {
        if ($this->newsletters->removeElement($newsletter)) {
            $newsletter->removeImage($this);
        }

        return $this;
    }
}
