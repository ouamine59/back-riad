<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\OrdersRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OrdersRepository::class)]
#[ApiResource]
class Orders
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $isCreatedAt = null;

    #[ORM\ManyToOne(inversedBy: 'user')]
    #[ORM\JoinColumn(nullable: false)]
    private ?States $states = null;

    #[ORM\ManyToOne(inversedBy: 'orders')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    /**
     * @var Collection<int, RowsOrder>
     */
    #[ORM\OneToMany(targetEntity: RowsOrder::class, mappedBy: 'orders')]
    private Collection $rowsOrders;

    public function __construct()
    {
        $this->rowsOrders = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIsCreatedAt(): ?\DateTimeImmutable
    {
        return $this->isCreatedAt;
    }

    public function setIsCreatedAt(\DateTimeImmutable $isCreatedAt): static
    {
        $this->isCreatedAt = $isCreatedAt;

        return $this;
    }

    public function getStates(): ?States
    {
        return $this->states;
    }

    public function setStates(?States $states): static
    {
        $this->states = $states;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return Collection<int, RowsOrder>
     */
    public function getRowsOrders(): Collection
    {
        return $this->rowsOrders;
    }

    public function addRowsOrder(RowsOrder $rowsOrder): static
    {
        if (!$this->rowsOrders->contains($rowsOrder)) {
            $this->rowsOrders->add($rowsOrder);
            $rowsOrder->setOrders($this);
        }

        return $this;
    }

    public function removeRowsOrder(RowsOrder $rowsOrder): static
    {
        if ($this->rowsOrders->removeElement($rowsOrder)) {
            // set the owning side to null (unless already changed)
            if ($rowsOrder->getOrders() === $this) {
                $rowsOrder->setOrders(null);
            }
        }

        return $this;
    }
}
