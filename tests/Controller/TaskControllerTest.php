<?php

namespace App\Tests\Controller;

use App\Entity\Task;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TaskControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private Router $urlGenerator;
    private EntityManagerInterface $em;
    private User $user;

    public function setUp(): void
    {
        $this->client = self::createClient();
        $this->urlGenerator = $this->client->getContainer()->get('router.default');
        $this->em = $this->client->getContainer()->get('doctrine.orm.entity_manager');
        $this->user = $this->em->find(User::class, 1);
    }

    public function testShowTaskPageNotConnected(): void
    {
        $this->client->request(Request::METHOD_GET, $this->urlGenerator->generate('task_list'));

        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);
        $this->assertResponseRedirects($this->urlGenerator->generate('app_login'));
    }

    public function testShowTaskPage(): void
    {
        $this->client->loginUser($this->user);

        $this->client->request(Request::METHOD_GET, $this->urlGenerator->generate('task_list'));
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    public function testCreateTaskNotConnected(): void
    {
        $this->client->request(Request::METHOD_GET, $this->urlGenerator->generate('task_create'));
        
        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);
        $this->assertResponseRedirects($this->urlGenerator->generate('app_login'));
    }

    public function testCreateTask(): void
    {
        $this->client->loginUser($this->user);

        $crawler = $this->client->request(Request::METHOD_GET, $this->urlGenerator->generate('task_create'));
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $form = $crawler->filter('form[name=task]')->form([
            'task[title]' => 'Une nouvelle tache',
            'task[content]' => 'Voici se qu\'il y a à faire pour vendredi',
        ]);

        $this->client->submit($form);

        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);
        $this->client->followRedirect();

        $this->assertSelectorTextContains('div.alert-success', 'Superbe ! La tâche a bien été ajoutée.');

        $this->assertRouteSame('task_list');
    }

    public function testUpdateTaskNotConnected(): void
    {
        $this->client->request(Request::METHOD_GET, $this->urlGenerator->generate('task_edit', ['id' => '1']));
        
        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);
        $this->assertResponseRedirects($this->urlGenerator->generate('app_login'));
    }

    public function testUpdateTask(): void
    {
        $this->client->loginUser($this->user);

        $task = $this->em->getRepository(Task::class)->findOneBy([]);

        $crawler = $this->client->request(
            Request::METHOD_GET,
            $this->urlGenerator->generate('task_edit', ['id' => $task->getId()])
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $form = $crawler->filter('form[name=task]')->form([
            'task[title]' => 'Une nouvelle tache',
            'task[content]' => 'Voici se qu\'il y a à faire pour Lundi',
        ]);

        $this->client->submit($form);

        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);

        $this->client->followRedirect();

        $this->assertSelectorTextContains('div.alert-success', 'Superbe ! La tâche a bien été modifiée.');

        $this->assertRouteSame('task_list');
    }

    public function testRemoveTaskNotConnected(): void
    {
        $this->client->request(Request::METHOD_GET, $this->urlGenerator->generate('task_delete', ['id' => '1']));
        
        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);
        $this->assertResponseRedirects($this->urlGenerator->generate('app_login'));
    }

    public function testRemoveTask(): void
    {
        $this->client->loginUser($this->user);

        $task = $this->em->getRepository(Task::class)->findOneBy([]);

        $this->client->request(
            Request::METHOD_GET,
            $this->urlGenerator->generate('task_delete', ['id' => $task->getId()])
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);

        $this->client->followRedirect();

        $this->assertSelectorTextContains('div.alert-success', 'Superbe ! La tâche a bien été supprimée.');

        $this->assertRouteSame('task_list');
    }

    public function testToggleTaskToDone(): void
    {
        $this->client->loginUser($this->user);

        $task = $this->em->getRepository(Task::class)->findOneBy([
            'isDone' => false,
        ]);

        $this->client->request(
            Request::METHOD_GET,
            $this->urlGenerator->generate('task_toggle', ['id' => $task->getId()])
        );

        $this->client->followRedirect();

        $this->assertSelectorTextContains('div.alert', 'Superbe ! La tâche '.$task->getTitle().' a bien été marquée comme faite.');
    }

    public function testToggleTaskToNotDone(): void
    {
        $this->client->loginUser($this->user);

        $task = $this->em->getRepository(Task::class)->findOneBy([
            'isDone' => true,
        ]);

        $this->client->request(
            Request::METHOD_GET,
            $this->urlGenerator->generate('task_toggle', ['id' => $task->getId()])
        );

        $this->client->followRedirect();

        $this->assertSelectorTextContains('div.alert', 'Superbe ! La tâche '.$task->getTitle().' a bien été marquée comme à faire.');
    }
}
