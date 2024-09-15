<?php

namespace App\DataFixtures;

use Faker\Factory;
use App\Entity\Task;
use App\Entity\User;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(private UserPasswordHasherInterface $userPasswordHasher)
    {
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();

        $admin = new User();
        $admin->setUsername('admin');
        $admin->setPassword($this->userPasswordHasher->hashPassword($admin, 'test'));
        $admin->setEmail('admin@test.fr');
        $admin->setRoles(['ROLE_ADMIN']);
        $manager->persist($admin);

        $user = new User();
        $user->setUsername('user');
        $user->setPassword($this->userPasswordHasher->hashPassword($user, 'test'));
        $user->setEmail('user@test.fr');
        $user->setRoles([]);
        $manager->persist($user);

        $task1 = new Task();
        $task1->setTitle($faker->title(3));
        $task1->setContent($faker->text(100));
        $task1->setCreatedAt($faker->dateTime());
        $task1->setUser($admin);
        $manager->persist($task1);

        $task2 = new Task();
        $task2->setTitle($faker->title(3));
        $task2->setContent($faker->text(100));
        $task2->setCreatedAt($faker->dateTime());
        $task2->setUser($admin);
        $manager->persist($task2);

        $manager->flush();
    }
}
