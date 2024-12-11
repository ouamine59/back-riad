<?php

namespace App\Tests\Entity;
use App\Entity\States;
use App\Entity\Orders;
use PHPUnit\Framework\TestCase;

class StatesTest extends TestCase
{
    public function testStatesEntity()
    {
        $state = new States();
        $state->setStates('Processing');

        $this->assertEquals('Processing', $state->getStates());
        $this->assertCount(0, $state->getOrders());

        $order = new Orders();
        $state->addOrder($order);

        $this->assertCount(1, $state->getOrders());
        $this->assertSame($state, $order->getStates());

        $state->removeOrder($order);

        $this->assertCount(0, $state->getOrders());
        $this->assertNull($order->getStates());
    }
}