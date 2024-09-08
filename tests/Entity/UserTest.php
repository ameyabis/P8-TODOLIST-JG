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
            // password, encrypter
            ->setPassword('test')
            ->setRoles([])
            ->setEmail('test@test.fr');
    }

    public function testEntityIsValid(): void
    {
        self::bootKernel();
        $container = self::getContainer();

        $user = $this->getEntity();

        $errors = $container->get('validator')->validate($user);

        $this->assertCount(0, $errors);
    }

    public function testInvalidUsernameNotBlank(): void
    {
        self::bootKernel();
        $container = self::getContainer();

        $user = $this->getEntity();
        $user->setUsername('');

        $errors = $container->get('validator')->validate($user);

        $this->assertCount(1, $errors);
    }

    public function testInvalidFormatEmail(): void
    {
        self::bootKernel();
        $container = self::getContainer();

        $user = $this->getEntity();
        $user->setEmail('');

        $errors = $container->get('validator')->validate($user);

        $this->assertCount(1, $errors);
    }

    public function testInvalidEmailLimit(): void
    {
        self::bootKernel();
        $container = self::getContainer();

        $user = $this->getEntity();
        $user->setEmail('testemail');

        $errors = $container->get('validator')->validate($user);

        $this->assertCount(1, $errors);
    }
}
