<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ConfigurationRepository")
 */
class Configuration
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
    private $days;

    /**
     * @ORM\Column(type="time", nullable=true)
     */
    private $openAt;

    /**
     * @ORM\Column(type="time", nullable=true)
     */
    private $closeAt;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDays(): ?string
    {
        return $this->days;
    }

    public function setDays(string $days): self
    {
        $this->days = $days;

        return $this;
    }

    public function getOpenAt(): ?\DateTimeInterface
    {
        return $this->openAt;
    }

    public function setOpenAt(\DateTimeInterface $openAt): self
    {
        $this->openAt = $openAt;

        return $this;
    }

    public function getCloseAt(): ?\DateTimeInterface
    {
        return $this->closeAt;
    }

    public function setCloseAt(?\DateTimeInterface $closeAt): self
    {
        $this->closeAt = $closeAt;

        return $this;
    }
}
