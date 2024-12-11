<?php namespace App\Tests\Entity;

use App\Entity\Products;
use App\Entity\Categories; // Assurez-vous que le namespace est correct
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ProductsTest extends KernelTestCase
{
    public function testProductValidation()
    {
        $product = new Products();
        $product->setTitle('T') // Invalid title (too short)
                ->setPrice('-10') // Invalid price (negative)
                ->setDiscount('not-a-boolean') // Invalid discount (not a boolean)
                ->setPriceDiscount('-5') // Invalid priceDiscount (negative)
                ->setDescription('Invalid!@#') // Invalid characters in description
                ->setActivied(1); // Invalid null boolean

        $validator = self::getContainer()->get('validator');
        $errors = $validator->validate($product);

        // Assert that validation fails
        $this->assertGreaterThan(0, count($errors), 'Validation should fail with invalid data.');

        foreach ($errors as $error) {
            echo $error->getPropertyPath() . ': ' . $error->getMessage() . PHP_EOL;
        }
    }

    public function testValidProduct()
    {
        // Mock une catégorie valide
        $category = $this->createMock(Categories::class);
    
        $product = new Products();
        $product->setTitle('Valid Product') // Titre valide
                ->setPrice('100') // Prix valide
                ->setDiscount(true) // Booléen valide
                ->setPriceDiscount('50') // Prix avec réduction valide
                ->setDescription('This-is-a-valid-description') // Description valide
                ->setActivied(true) // Booléen valide
                ->setCategories($category); // Catégorie valide
    
        $validator = self::getContainer()->get('validator');
        $errors = $validator->validate($product);
    
        // Afficher les erreurs pour débogage
        foreach ($errors as $error) {
            echo $error->getPropertyPath() . ': ' . $error->getMessage() . PHP_EOL;
        }
    
        // Vérifier que la validation passe
        $this->assertCount(0, $errors, 'Validation should pass with valid data.');
    }
}