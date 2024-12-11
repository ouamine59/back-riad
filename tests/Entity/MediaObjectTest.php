<?php

namespace App\Tests\Entity;

use App\Entity\MediaObject;
use App\Entity\Products;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\File;

class MediaObjectTest extends TestCase
{
    public function testGetAndSetFile(): void
    {
        $mediaObject = new MediaObject();
        $file = new File(sys_get_temp_dir() . '/test_file.txt', false);

        $mediaObject->setFile($file);
        
        $this->assertSame($file, $mediaObject->getFile());
        
    }

    public function testGetAndSetFilePath(): void
    {
        $mediaObject = new MediaObject();
        $filePath = 'uploads/test_file.txt';

        $mediaObject->setFilePath($filePath);

        $this->assertSame($filePath, $mediaObject->getFilePath());
    }

    public function testGetAndSetProducts(): void
    {
        $mediaObject = new MediaObject();
        $product = new Products();

        $mediaObject->setProducts($product);

        $this->assertSame($product, $mediaObject->getProducts());
    }
}