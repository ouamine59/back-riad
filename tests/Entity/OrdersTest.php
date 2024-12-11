<?php

namespace App\Tests\Entity;

use App\Entity\Orders;
use App\Entity\RowsOrder;
use App\Entity\States;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

class OrdersTest extends TestCase
{
    public function testSettersAndGetters(): void
    {
        $orders = new Orders();
        $createdAt = new \DateTimeImmutable();
        $state = $this->createMock(States::class);
        $user = $this->createMock(User::class);

        // Test de l'attribut isCreatedAt
        $orders->setIsCreatedAt($createdAt);
        $this->assertSame($createdAt, $orders->getIsCreatedAt());

        // Test de l'attribut states
        $orders->setStates($state);
        $this->assertSame($state, $orders->getStates());

        // Test de l'attribut user
        $orders->setUser($user);
        $this->assertSame($user, $orders->getUser());
    }

    public function testRowsOrdersCollection(): void
    {
        $orders = new Orders();
        $rowsOrder1 = $this->createMock(RowsOrder::class);
        $rowsOrder2 = $this->createMock(RowsOrder::class);

        // Test ajout de RowsOrder
        $orders->addRowsOrder($rowsOrder1);
        $orders->addRowsOrder($rowsOrder2);
        $this->assertCount(2, $orders->getRowsOrders());
        $this->assertTrue($orders->getRowsOrders()->contains($rowsOrder1));
        $this->assertTrue($orders->getRowsOrders()->contains($rowsOrder2));

        // Test suppression de RowsOrder
        $orders->removeRowsOrder($rowsOrder1);
        $this->assertCount(1, $orders->getRowsOrders());
        $this->assertFalse($orders->getRowsOrders()->contains($rowsOrder1));
    }
}