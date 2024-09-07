<?php

namespace App\Tests\Entity;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class UserTest extends KernelTestCase
{
    public function getEntity(): User
    {
        return (new User())
            ->setUsername('test')
            //password, encrypter
            ->setPassword('')
            ->setRoles([])
            ->setEmail('test@test.fr');
    }

    public function testEntityIsValid(): void
    {
        self::bootKernel();
        $container = static::getContainer();

        $task = $this->getEntity();

        $errors = $container->get('validator')->validate($task);

        $this->assertCount(0, $errors);
    }

    // public function testInvalidUsernameNotBlank
    // public function testInvalidUsernameLimit

    // public function testInvalidPassword

    // public function testInvalidFormatEmail
    // public function testInvalidEmailLimit
}
