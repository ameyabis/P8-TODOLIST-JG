<?php

namespace App\Tests\Controller;

use App\Entity\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class HomepageControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private Router $urlGenerator;

    public function setUp(): void
    {
        $this->client = self::createClient();
        $this->urlGenerator = $this->client->getContainer()->get('router.default');
    }

    public function testShowHomePageRedirectNoConnect(): void
    {
        $this->client->request(Request::METHOD_GET, $this->urlGenerator->generate('homepage'));

        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);
    }

    public function testShowHomePageConnected(): void
    {
        $em = $this->client->getContainer()->get('doctrine.orm.entity_manager');

        $user = $em->find(User::class, 1);
        $this->client->loginUser($user);

        $this->client->request(Request::METHOD_GET, $this->urlGenerator->generate('homepage'));

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertSelectorTextContains('h1', 'Bienvenue sur Todo List, l\'application vous permettant de gérer l\'ensemble de vos tâches sans effort !');
    }
}
