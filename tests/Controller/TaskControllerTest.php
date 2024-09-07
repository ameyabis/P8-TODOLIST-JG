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

    public function setUp(): void
    {
        $this->client = self::createClient();
        $this->urlGenerator = $this->client->getContainer()->get('router.default');
        $this->em = $this->client->getContainer()->get('doctrine.orm.entity_manager');
    }

    public function testShowTaskPage(): void
    {
        $user = $this->em->find(User::class, 1);
        $this->client->loginUser($user);

        $this->client->request(Request::METHOD_GET, $this->urlGenerator->generate('task_list'));
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    public function testCreateTask(): void
    {
        $user = $this->em->find(User::class, 1);
        $this->client->loginUser($user);

        $crawler = $this->client->request(Request::METHOD_GET, $this->urlGenerator->generate('task_create'));
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $form = $crawler->filter('form[name=task]')->form([
            'task[title]' => 'Une nouvelle tache',
            'task[content]' => 'Voici se qu\'il y a à faire pour vendredi'
        ]);

        $this->client->submit($form);

        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);
        $this->client->followRedirect();

        $this->assertSelectorTextContains('div.alert-success', 'Superbe ! La tâche a bien été ajoutée.');

        $this->assertRouteSame('task_list');
    }

    public function testUpdateTask(): void
    {
        $user = $this->em->find(User::class, 1);
        $this->client->loginUser($user);

        $task = $this->em->getRepository(Task::class)->findOneBy([
            'id' => 1,
        ]);

        $crawler = $this->client->request(
            Request::METHOD_GET,
            $this->urlGenerator->generate('task_edit', ['id' => $task->getId()])
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $form = $crawler->filter('form[name=task]')->form([
            'task[title]' => 'Une nouvelle tache',
            'task[content]' => 'Voici se qu\'il y a à faire pour Lundi'
        ]);

        $this->client->submit($form);

        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);

        $this->client->followRedirect();

        $this->assertSelectorTextContains('div.alert-success', 'Superbe ! La tâche a bien été modifiée.');

        $this->assertRouteSame('task_list');
    }

    public function testRemoveTask(): void
    {
        $user = $this->em->find(User::class, 1);
        $this->client->loginUser($user);

        $task = $this->em->getRepository(Task::class)->findOneBy([
            'id' => 2,
        ]);
        
        $crawler = $this->client->request(
            Request::METHOD_GET,
            $this->urlGenerator->generate('task_delete', ['id' => $task->getId()])
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);

        $this->client->followRedirect();

        $this->assertSelectorTextContains('div.alert-success', 'Superbe ! La tâche a bien été supprimée.');

        $this->assertRouteSame('task_list');
    }
}
