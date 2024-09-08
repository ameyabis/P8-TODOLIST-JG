<?php

namespace App\Controller;

use App\Entity\Task;
use App\Entity\User;
use App\Form\TaskType;
use App\Service\TaskService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class TaskController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private TaskService $taskService,
    ) {
    }

    #[Route(path: '/tasks', name: 'task_list')]
    public function listTasks(Request $request): Response
    {
        $done = $request->query->get('done');

        $task = $this->em->getRepository(Task::class)->findBy(['isDone' => $done]);

        return $this->render('task/list.html.twig', ['tasks' => $task]);
    }

    #[Route(path: '/tasks/create', name: 'task_create')]
    public function createTask(
        Request $request,
        #[CurrentUser] ?User $currentUser,
    ): Response {
        $task = new Task();
        $form = $this->createForm(TaskType::class, $task);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $task->setUser($currentUser);
            $this->taskService->save($task);

            $this->addFlash('success', 'La tâche a bien été ajoutée.');

            return $this->redirectToRoute('task_list', ['done' => $task->isDone()]);
        }

        return $this->render('task/create.html.twig', ['form' => $form->createView()]);
    }

    #[Route(path: '/tasks/{id}/edit', name: 'task_edit')]
    public function editTask(Task $task, Request $request): Response
    {
        $form = $this->createForm(TaskType::class, $task);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->taskService->save($task);

            $this->addFlash('success', 'La tâche a bien été modifiée.');

            return $this->redirectToRoute('task_list', ['done' => $task->isDone()]);
        }

        return $this->render('task/edit.html.twig', [
            'form' => $form->createView(),
            'task' => $task,
        ]);
    }

    #[Route(path: '/tasks/{id}/toggle', name: 'task_toggle')]
    public function toggleTask(Task $task): Response
    {
        $task->toggle(!$task->isDone());
        $this->taskService->save($task);

        $message = $task->isDone() ? 'faite' : 'à faire';
        $this->addFlash('success', sprintf('La tâche %s a bien été marquée comme %s.', $task->getTitle(), $message));

        return $this->redirectToRoute('task_list', ['done' => !$task->isDone()]);
    }

    #[Route(path: '/tasks/{id}/delete', name: 'task_delete')]
    public function deleteTask(
        Task $task,
        #[CurrentUser] ?User $currentUser,
    ): Response {
        if ($currentUser === $task->getUser() || (null === $task->getUser() && $this->isGranted('ROLE_ADMIN'))) {
            $this->taskService->remove($task);

            $this->addFlash('success', 'La tâche a bien été supprimée.');
        }

        return $this->redirectToRoute('task_list', ['done' => $task->isDone()]);
    }
}
