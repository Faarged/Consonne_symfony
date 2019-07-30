<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UsersRepository")
 * @UniqueEntity(
 *         fields= {"pseudo"},
 *         message= "Ce pseudo est déjà pris",
 *                {"cardNum"},
 *        message= "Ce numéro est déjà attribué"
 *
 *)
 */
class Users implements UserInterface
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
    private $name;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $firstname;

    /**
     * @ORM\Column(type="date")
     */
    private $birthDate;

    /**
     * @ORM\Column(type="datetime")
     */
    private $subAt;

    /**
     * @ORM\Column(type="string", length=255)
     *
     */
    private $pseudo;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $cardNum;

    /**
     * @ORM\Column(type="integer")
     */
    private $pegi;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $statut;

    /**
     * @ORM\Column(type="datetime")
     */
    private $endSubAt;

    /**
     * @ORM\Column(type="time", nullable=true)
     */
    private $gameTime;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\Length(min="7", minMessage="Votre mot de passe doit comporter au moins 7 caractères")
     */
    private $password;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Reservation", mappedBy="user", orphanRemoval=true)
     */
    private $reservations;

    /**
    * @Assert\EqualTo(propertyPath="password", message="erreur de confirmation du mot de passe")
    */
    public $confirm_password;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Role", inversedBy="users")
     */
    private $userRoles;

    public function __construct()
    {
        $this->reservations = new ArrayCollection();
        $this->userRoles = new ArrayCollection();

    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(string $firstname): self
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getBirthDate(): ?\DateTimeInterface
    {
        return $this->birthDate;
    }

    public function setBirthDate(\DateTimeInterface $birthDate): self
    {
        $this->birthDate = $birthDate;

        return $this;
    }

    public function getSubAt(): ?\DateTimeInterface
    {
        return $this->subAt;
    }

    public function setSubAt(\DateTimeInterface $subAt): self
    {
        $this->subAt = $subAt;

        return $this;
    }

    public function getPseudo(): ?string
    {
        return $this->pseudo;
    }

    public function setPseudo(string $pseudo): self
    {
        $this->pseudo = $pseudo;

        return $this;
    }

    public function getCardNum(): ?string
    {
        return $this->cardNum;
    }

    public function setCardNum(string $cardNum): self
    {
        $this->cardNum = $cardNum;

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

    public function getStatut(): ?string
    {
        return $this->statut;
    }

    public function setStatut(string $statut): self
    {
        $this->statut = $statut;

        return $this;
    }

    public function getEndSubAt(): ?\DateTimeInterface
    {
        return $this->endSubAt;
    }

    public function setEndSubAt(\DateTimeInterface $endSubAt): self
    {
        $this->endSubAt = $endSubAt;

        return $this;
    }

    public function getGameTime(): ?\DateTimeInterface
    {
        return $this->gameTime;
    }

    public function setGameTime(?\DateTimeInterface $gameTime): self
    {
        $this->gameTime = $gameTime;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function eraseCredentials(){}
    public function getSalt(){}
    public function getRoles(){
        $roles = $this->userRoles->map(function($role){
          return $role->getLabel();
        })->toArray();
        
         // guarantee every user at least has ROLE_USER
         $roles[] = 'ROLE_USER';

         return $roles;
    }
    public function getUsername() { return $this->pseudo; }

    /**
     * @return Collection|Reservation[]
     */
    public function getReservations(): Collection
    {
        return $this->reservations;
    }

    public function addReservation(Reservation $reservation): self
    {
        if (!$this->reservations->contains($reservation)) {
            $this->reservations[] = $reservation;
            $reservation->setUser($this);
        }

        return $this;
    }

    public function removeReservation(Reservation $reservation): self
    {
        if ($this->reservations->contains($reservation)) {
            $this->reservations->removeElement($reservation);
            // set the owning side to null (unless already changed)
            if ($reservation->getUser() === $this) {
                $reservation->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Role[]
     */
    public function getUserRoles(): Collection
    {
        return $this->userRoles;
    }

    public function addUserRole(Role $userRole): self
    {
        if (!$this->userRoles->contains($userRole)) {
            $this->userRoles[] = $userRole;
        }

        return $this;
    }

    public function removeUserRole(Role $userRole): self
    {
        if ($this->userRoles->contains($userRole)) {
            $this->userRoles->removeElement($userRole);
        }

        return $this;
    }
}
