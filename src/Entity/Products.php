<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Repository\ProductsRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ProductsRepository::class)]
#[ApiResource(
    normalizationContext:['groups' => ['products:read']],
    denormalizationContext:['groups' => ['products:write']],
    operations: [
         new Get(
             uriTemplate: '/api/products/listing',
             name:'app_visitor_products_listing'
         ),
         new Post(
             security: "is_granted('ROLE_ADMIN')",
             uriTemplate: '/api/products/admin/create',
             name:'app_admin_products_create'
         ),
        new Put(
            security: "is_granted('ROLE_ADMIN')",
            uriTemplate: '/api/products/admin/update/{productsId}',
            name:'app_admin_products_update'
        ),
        new Put(
            security: "is_granted('ROLE_ADMIN')",
            uriTemplate: '/api/products/admin/states/update/{productsId}/{states}',
            name:'app_admin_products_states_update'
        ),
        new Get(
            security: "is_granted('ROLE_ADMIN')",
            uriTemplate: '/api/products/admin/listing',
            name:'app_admin_products_listing'
        ),
    ]
)]
class Products
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["products:read"])]
    private ?int $id = null;

    #[ORM\Column(length: 30)]
    #[Groups(["products:read", "products:write"])]
    #[Assert\NotBlank]
    #[Assert\Regex('/^[a-zA-Z0-9À-ÖØ-öø-ÿ\- ]{2,80}+$/')]
    #[Assert\NotBlank()]
    private ?string $title = null;

    #[ORM\Column(length: 6)]
    #[Groups(["products:read", "products:write"])]
    #[Assert\NotBlank]
    #[Assert\Regex('/^[0-9]{1,8}$/')]
    #[Assert\NotBlank()]
    private ?string $price = null;

    #[ORM\Column]
    #[Groups(["products:read", "products:write"])]
    #[Assert\Type(
        type: 'bool'
    )]
    private ?bool $discount = null;

    #[ORM\Column(length: 6, nullable: true)]
    #[Groups(["products:read", "products:write"])]
    #[Assert\Regex('/^[0-9]{1,8}$/')]
    private ?string $priceDiscount = null;

    #[ORM\Column(length: 200, nullable: true)]
    #[Groups(["products:read", "products:write"])]
    #[Assert\Regex('/^[a-zA-Z0-9À-ÖØ-öø-ÿ\- ]{2,200}?$/')]
    private ?string $description = null;

    #[ORM\Column]
    #[Groups(["products:read", "products:write"])]
    #[Assert\Type(
        type: 'bool'
    )]
    #[Assert\NotBlank()]
    private ?bool $isActivied = null;

    #[ORM\ManyToOne(inversedBy: 'products')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(["products:read", "products:write"])]
    private ?Categories $categories = null;

    /**
     * @var Collection<int, MediaObject>
     */
    #[ORM\OneToMany(targetEntity: MediaObject::class, mappedBy: 'products')]
    #[Groups(["products:read", "products:write"])]
    private Collection $mediaObjects;

    /**
     * @var Collection<int, RowsOrder>
     */
    #[ORM\OneToMany(targetEntity: RowsOrder::class, mappedBy: 'products')]
    #[Groups(["products:read", "products:write"])]
    private Collection $rowsOrders;

    public function __construct()
    {
        $this->mediaObjects = new ArrayCollection();
        $this->rowsOrders   = new ArrayCollection();
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
