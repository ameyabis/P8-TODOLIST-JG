<?php

namespace App\Tests\Entity;

use App\Entity\Task;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class TaskTest extends KernelTestCase
{
    public function getEntity(): Task
    {
        return (new Task())
            ->setTitle('Test')
            ->setContent('Voila les tests que l\'on peut faire')
            ->setCreatedAt(new \DateTime())
            ->toggle(false);
    }

    public function testEntityIsValid(): void
    {
        self::bootKernel();
        $container = static::getContainer();

        $task = $this->getEntity();

        $errors = $container->get('validator')->validate($task);

        $this->assertCount(0, $errors);
    }

    public function testInvalidTitle(): void
    {
        self::bootKernel();
        $container = static::getContainer();

        $task = $this->getEntity();
        $task->setTitle('');

        $errors = $container->get('validator')->validate($task);

        $this->assertCount(1, $errors);
    }

    public function testInvalidContent(): void
    {
        self::bootKernel();
        $container = static::getContainer();

        $task = $this->getEntity();
        $task->setContent('');

        $errors = $container->get('validator')->validate($task);

        $this->assertCount(1, $errors);
    }
}
