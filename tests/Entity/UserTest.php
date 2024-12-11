<?php

namespace App\Tests\Entity ;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class UserTest extends KernelTestCase
{
    public function testUserValidation()
{
    $user = new User();
    $user->setEmail('invalid-email')
        ->setPassword('weakpass')
        ->setPhone('123')
        ->setFirstName('J')
        ->setLastName('D')
        ->setAdress('');

    $validator = self::getContainer()->get('validator');
    $errors = $validator->validate($user);

    $this->assertGreaterThan(0, count($errors), 'Validation should fail with invalid data.');
}
}
