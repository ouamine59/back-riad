<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\ProductsRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProductsRepository::class)]
#[ApiResource]
class Products
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 30)]
    private ?string $title = null;

    #[ORM\Column(length: 6)]
    private ?string $price = null;

    #[ORM\Column]
    private ?bool $discount = null;

    #[ORM\Column(length: 6, nullable: true)]
    private ?string $priceDiscount = null;

    #[ORM\Column(length: 200, nullable: true)]
    private ?string $description = null;

    #[ORM\Column]
    private ?bool $isActivied = null;

    #[ORM\ManyToOne(inversedBy: 'products')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Categories $categories = null;

    /**
     * @var Collection<int, MediaObject>
     */
    #[ORM\OneToMany(targetEntity: MediaObject::class, mappedBy: 'products')]
    private Collection $mediaObjects;

    /**
     * @var Collection<int, RowsOrder>
     */
    #[ORM\OneToMany(targetEntity: RowsOrder::class, mappedBy: 'products')]
    private Collection $rowsOrders;

    public function __construct()
    {
        $this->mediaObjects = new ArrayCollection();
        $this->rowsOrders = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

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

    public function isDiscount(): ?bool
    {
        return $this->discount;
    }

    public function setDiscount(bool $discount): static
    {
        $this->discount = $discount;

        return $this;
    }

    public function getPriceDiscount(): ?string
    {
        return $this->priceDiscount;
    }

    public function setPriceDiscount(?string $priceDiscount): static
    {
        $this->priceDiscount = $priceDiscount;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function isActivied(): ?bool
    {
        return $this->isActivied;
    }

    public function setActivied(bool $isActivied): static
    {
        $this->isActivied = $isActivied;

        return $this;
    }

    public function getCategories(): ?Categories
    {
        return $this->categories;
    }

    public function setCategories(?Categories $categories): static
    {
        $this->categories = $categories;

        return $this;
    }

    /**
     * @return Collection<int, MediaObject>
     */
    public function getMediaObjects(): Collection
    {
        return $this->mediaObjects;
    }

    public function addMediaObject(MediaObject $mediaObject): static
    {
        if (!$this->mediaObjects->contains($mediaObject)) {
            $this->mediaObjects->add($mediaObject);
            $mediaObject->setProducts($this);
        }

        return $this;
    }

    public function removeMediaObject(MediaObject $mediaObject): static
    {
        if ($this->mediaObjects->removeElement($mediaObject)) {
            // set the owning side to null (unless already changed)
            if ($mediaObject->getProducts() === $this) {
                $mediaObject->setProducts(null);
            }
        }

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
            $rowsOrder->setProducts($this);
        }

        return $this;
    }

    public function removeRowsOrder(RowsOrder $rowsOrder): static
    {
        if ($this->rowsOrders->removeElement($rowsOrder)) {
            // set the owning side to null (unless already changed)
            if ($rowsOrder->getProducts() === $this) {
                $rowsOrder->setProducts(null);
            }
        }

        return $this;
    }
}
