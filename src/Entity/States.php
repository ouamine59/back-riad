<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\StatesRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: StatesRepository::class)]
#[ApiResource]
class States
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 20)]
    private ?string $states = null;

    /**
     * @var Collection<int, Orders>
     */
    #[ORM\OneToMany(targetEntity: Orders::class, mappedBy: 'states')]
    private Collection $user;

    public function __construct()
    {
        $this->user = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStates(): ?string
    {
        return $this->states;
    }

    public function setStates(string $states): static
    {
        $this->states = $states;

        return $this;
    }

    /**
     * @return Collection<int, Orders>
     */
    public function getUser(): Collection
    {
        return $this->user;
    }

    public function addUser(Orders $user): static
    {
        if (!$this->user->contains($user)) {
            $this->user->add($user);
            $user->setStates($this);
        }

        return $this;
    }

    public function removeUser(Orders $user): static
    {
        if ($this->user->removeElement($user)) {
            // set the owning side to null (unless already changed)
            if ($user->getStates() === $this) {
                $user->setStates(null);
            }
        }

        return $this;
    }
}
