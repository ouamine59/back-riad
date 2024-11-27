<?php

namespace App\Entity;

use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Delete;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\OrdersRepository;
use ApiPlatform\Metadata\ApiResource;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: OrdersRepository::class)]
#[ApiResource(
    normalizationContext:['groups' => ['orders:read']],
    denormalizationContext:['groups' => ['orders:write']],
    operations: [
         new Get(
            security: "is_granted('ROLE_CLIENT')",
            uriTemplate: '/api/orders/listing/{idUser}',
            name:'app_client_orders_listing'
         ),
         new Get(
            security: "is_granted('ROLE_CLIENT')",
            uriTemplate: '/api/orders/detail/{idUser}/{idOrder}',
            name:'app_client_orders_detail'
        ),
        new Delete(
           security: "is_granted('ROLE_CLIENT')",
           uriTemplate: '/api/orders/ddelete/{idUser}/{idOrder}',
           name:'app_client_orders_delete'
       )
       ,
        new Post(
           security: "is_granted('ROLE_CLIENT')",
           uriTemplate: '/api/orders/create/{idUser}',
           name:'app_client_orders_create'
       )
       ,
        new Post(
           security: "is_granted('ROLE_ADMIN')",
           uriTemplate: '/api/orders/admin/listing',
           name:'app_admin_orders_listing'
       )
    ]
)]
class Orders
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["orders:read"])]
    private ?int $id = null;

    #[ORM\Column]
    #[Groups(["orders:read", "orders:write"])]
    private ?\DateTimeImmutable $isCreatedAt = null;

    #[ORM\ManyToOne(inversedBy: 'orders')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(["orders:read", "orders:write"])]
    private ?States $states = null;

    #[ORM\ManyToOne(inversedBy: 'orders')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(["orders:read", "orders:write"])]
    private ?User $user = null;

    /**
     * @var Collection<int, RowsOrder>
     */
    #[Groups(["orders:read", "orders:write"])]
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
