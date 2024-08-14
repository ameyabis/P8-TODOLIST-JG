<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Service\UserService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private UserPasswordHasherInterface $userPasswordHasher,
        private UserService $userService,
    ) {
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route(path: '/users', name: 'user_list')]
    public function listUser(): Response
    {
        $users = $this->em->getRepository(User::class)->findAll();

        return $this->render('user/list.html.twig', ['users' => $users]);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route(path: '/users/create', name: 'user_create')]
    public function createUser(Request $request): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword($this->userPasswordHasher->hashPassword($user, $user->getPassword()));

            $this->userService->save($user);

            $this->addFlash('success', 'L\'utilisateur a bien été ajouté.');

            return $this->redirectToRoute('user_list');
        }

        return $this->render('user/create.html.twig', ['form' => $form->createView()]);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route(path: '/users/{id}/edit', name: 'user_edit')]
    public function editUser(User $user, Request $request): Response
    {
        $form = $this->createForm(UserType::class, $user);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword($this->userPasswordHasher->hashPassword($user, $user->getPassword()));

            $this->userService->save($user);

            $this->addFlash('success', 'L\'utilisateur a bien été modifié');

            return $this->redirectToRoute('user_list');
        }

        return $this->render('user/edit.html.twig', ['form' => $form->createView(), 'user' => $user]);
    }
}
