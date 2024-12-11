<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\RowsOrderRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: RowsOrderRepository::class)]
#[ApiResource]
class RowsOrder
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'rowsOrders')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Orders $orders = null;

    #[ORM\ManyToOne(inversedBy: 'rowsOrders')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Products $products = null;

    #[ORM\Column]
    #[Assert\Type(
        type: 'integer'
    )]
    #[Assert\Positive()]
    private ?int $amount = null;

    #[ORM\Column(length: 6)]
    #[Assert\NotBlank]
    #[Assert\Regex('/^[0-9]{1,8}+$/')]
    private ?string $price = null; 

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOrders(): ?Orders
    {
        return $this->orders;
    }

    public function setOrders(?Orders $orders): static
    {
        $this->orders = $orders;

        return $this;
    }

    public function getProducts(): ?Products
    {
        return $this->products;
    }

    public function setProducts(?Products $products): static
    {
        $this->products = $products;

        return $this;
    }

    public function getAmount(): ?int
    {
        return $this->amount;
    }

    public function setAmount(int $amount): static
    {
        $this->amount = $amount;

        return $this;
    }

    public function getPrice(): ?string
    {
        return $this->price;
    }

    public function setPrice(string $price): static
    {
        $this->price = $price;

        return $this;
    }
}
