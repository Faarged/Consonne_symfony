<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\GameRepository")
 */
class Game
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $title;

    /**
     * @ORM\Column(type="integer")
     */
    private $pegi;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Materiel", inversedBy="games")
     */
    private $support;

    public function __construct()
    {
        $this->support = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getPegi(): ?int
    {
        return $this->pegi;
    }

    public function setPegi(int $pegi): self
    {
        $this->pegi = $pegi;

        return $this;
    }

    /**
     * @return Collection|Materiel[]
     */
    public function getSupport(): Collection
    {
        return $this->support;
    }

    public function addSupport(Materiel $support): self
    {
        if (!$this->support->contains($support)) {
            $this->support[] = $support;
        }

        return $this;
    }

    public function removeSupport(Materiel $support): self
    {
        if ($this->support->contains($support)) {
            $this->support->removeElement($support);
        }

        return $this;
    }
    public function __toString() {
    return $this->title;
}
}
