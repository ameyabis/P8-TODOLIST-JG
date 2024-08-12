<?php

namespace App\Service;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class UserService
{
    public function __construct(
        private EntityManagerInterface $em,
        
    ){}

    public function save(User $user): void
    {
        $this->em->persist($user);
        $this->em->flush();
    }
}