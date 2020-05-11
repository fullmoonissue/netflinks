<?php

declare(strict_types=1);

namespace App\Domain\Entity;

use App\Infrastructure\Repository\CategoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Stringable;

#[ORM\Entity(repositoryClass: CategoryRepository::class)]
class Category implements Stringable
{
    #[ORM\Id]
    #[ORM\Column]
    private string $id = '';

    #[ORM\Column(length: 255)]
    private string $name;

    #[ORM\OneToMany(targetEntity: Link::class, mappedBy: 'category')]
    private Collection $links;

    #[ORM\ManyToMany(targetEntity: Newsletter::class, mappedBy: 'categories')]
    private Collection $newsletters;

    public function __construct()
    {
        $this->links = new ArrayCollection();
        $this->newsletters = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->name;
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

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Collection<int, Link>
     */
    public function getLinks(): Collection
    {
        return $this->links;
    }

    public function addLink(Link $link): self
    {
        if (!$this->links->contains($link)) {
            $this->links->add($link);
            $link->setCategory($this);
        }

        return $this;
    }

    public function removeLink(Link $link): self
    {
        $this->links->removeElement($link);

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
            $newsletter->addCategory($this);
        }

        return $this;
    }

    public function removeNewsletter(Newsletter $newsletter): self
    {
        if ($this->newsletters->removeElement($newsletter)) {
            $newsletter->removeCategory($this);
        }

        return $this;
    }
}
