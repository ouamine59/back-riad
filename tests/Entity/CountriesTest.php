<?php

namespace App\Tests\Entity;

use App\Entity\Countries;
use App\Entity\Cities;
use PHPUnit\Framework\TestCase;

class CountriesTest extends TestCase
{
    public function testCountriesProperties()
    {
        $country = new Countries();

        // Test de la propriété "countries"
        $country->setCountries('France');
        $this->assertEquals('France', $country->getCountries());
    }

    public function testCountriesCitiesRelation()
    {
        $country = new Countries();
        $city1 = new Cities();
        $city2 = new Cities();

        // Vérification initiale de la collection de villes
        $this->assertCount(0, $country->getCities());

        // Ajout de la première ville
        $country->addCity($city1);
        $this->assertCount(1, $country->getCities());
        $this->assertSame($country, $city1->getCountries());

        // Ajout de la deuxième ville
        $country->addCity($city2);
        $this->assertCount(2, $country->getCities());

        // Suppression d'une ville
        $country->removeCity($city1);
        $this->assertCount(1, $country->getCities());
        $this->assertNull($city1->getCountries());
    }
}