<?php

namespace App\Tests\Entity;

use App\Entity\Categories;
use App\Entity\Products;
use PHPUnit\Framework\TestCase;

class CategoriesTest extends TestCase
{
    public function testGetAndSetCategories(): void
    {
        $category = new Categories();
        $categoryName = 'Electronics';
        
        $category->setCategories($categoryName);
        
        $this->assertSame($categoryName, $category->getCategories());
    }

    public function testAddAndRemoveProduct(): void
    {
        $category = new Categories();
        $product = new Products();

        // Test adding a product
        $category->addProduct($product);
        
        $this->assertCount(1, $category->getProducts());
        $this->assertTrue($category->getProducts()->contains($product));
        $this->assertSame($category, $product->getCategories());

        // Test removing a product
        $category->removeProduct($product);
        
        $this->assertCount(0, $category->getProducts());
        $this->assertNull($product->getCategories());
    }
}