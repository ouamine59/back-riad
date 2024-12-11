<?php

namespace App\Tests\Entity;
use App\Entity\Cities;
use App\Entity\Countries;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

class CitiesTest extends TestCase
{
    public function testCitiesEntity()
    {
        $city = new Cities();

        // Test de la propriété `cities`
        $city->setCities('Paris');
        $this->assertEquals('Paris', $city->getCities());

        // Test de la propriété `zipCode`
        $city->setZipCode('75001');
        $this->assertEquals('75001', $city->getZipCode());

        // Test de la relation avec `Countries`
        $country = new Countries();
        $city->setCountries($country);
        $this->assertSame($country, $city->getCountries());

        // Test de la relation avec `User`
        $this->assertCount(0, $city->getUsers());

        $user = new User();
        $city->addUser($user);

        $this->assertCount(1, $city->getUsers());
        $this->assertSame($city, $user->getCities());

        $city->removeUser($user);
        $this->assertCount(0, $city->getUsers());
        $this->assertNull($user->getCities());
    }
}