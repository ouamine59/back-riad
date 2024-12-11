<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\CountriesRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
#[ORM\Entity(repositoryClass: CountriesRepository::class)]
#[ApiResource]
class Countries
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    #[Assert\Regex('/^[a-zA-ZÀ-ÖØ-öø-ÿ\-\_ ]{2,50}$/')]
    #[Assert\NotBlank()]
    private ?string $countries = null;

    /**
     * @var Collection<int, Cities>
     */
    #[ORM\OneToMany(targetEntity: Cities::class, mappedBy: 'countries')]
    private Collection $cities;

    public function __construct()
    {
        $this->cities = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCountries(): ?string
    {
        return $this->countries;
    }

    public function setCountries(string $countries): static
    {
        $this->countries = $countries;

        return $this;
    }

    /**
     * @return Collection<int, Cities>
     */
    public function getCities(): Collection
    {
        return $this->cities;
    }

    public function addCity(Cities $city): static
    {
        if (!$this->cities->contains($city)) {
            $this->cities->add($city);
            $city->setCountries($this);
        }

        return $this;
    }

    public function removeCity(Cities $city): static
    {
        if ($this->cities->removeElement($city)) {
            // set the owning side to null (unless already changed)
            if ($city->getCountries() === $this) {
                $city->setCountries(null);
            }
        }

        return $this;
    }
}
