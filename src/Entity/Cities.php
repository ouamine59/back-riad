<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\CitiesRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CitiesRepository::class)]
#[ApiResource]
class Cities
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 40)]
    private ?string $cities = null;

    #[ORM\Column(length: 5)]
    private ?string $zipCode = null;

    #[ORM\ManyToOne(inversedBy: 'cities')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Countries $countries = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCities(): ?string
    {
        return $this->cities;
    }

    public function setCities(string $cities): static
    {
        $this->cities = $cities;

        return $this;
    }

    public function getZipCode(): ?string
    {
        return $this->zipCode;
    }

    public function setZipCode(string $zipCode): static
    {
        $this->zipCode = $zipCode;

        return $this;
    }

    public function getCountries(): ?Countries
    {
        return $this->countries;
    }

    public function setCountries(?Countries $countries): static
    {
        $this->countries = $countries;

        return $this;
    }
}
