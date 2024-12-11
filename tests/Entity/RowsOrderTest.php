<?php

namespace App\Tests\Entity;

use App\Entity\Orders;
use App\Entity\Products;
use App\Entity\RowsOrder;
use PHPUnit\Framework\TestCase;

class RowsOrderTest extends TestCase
{
    public function testSettersAndGetters(): void
    {
        $rowsOrder = new RowsOrder();

        // Mock des entités liées
        $orders = $this->createMock(Orders::class);
        $products = $this->createMock(Products::class);

        // Test de l'attribut orders
        $rowsOrder->setOrders($orders);
        $this->assertSame($orders, $rowsOrder->getOrders());

        // Test de l'attribut products
        $rowsOrder->setProducts($products);
        $this->assertSame($products, $rowsOrder->getProducts());

        // Test de l'attribut amount
        $amount = 5;
        $rowsOrder->setAmount($amount);
        $this->assertSame($amount, $rowsOrder->getAmount());

        // Test de l'attribut price
        $price = '25.99';
        $rowsOrder->setPrice($price);
        $this->assertSame($price, $rowsOrder->getPrice());
    }
}