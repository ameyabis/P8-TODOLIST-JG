<?php

namespace App\Tests\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SecurityControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private Router $urlGenerator;

    public function setUp(): void
    {
        $this->client = self::createClient();
        $this->urlGenerator = $this->client->getContainer()->get('router.default');
    }

    public function testIfLoginIsSuccessful(): void
    {
        $crawler = $this->client->request(Request::METHOD_GET, $this->urlGenerator->generate('app_login'));

        $form = $crawler->filter('form[name=login]')->form([
            '_username' => 'ame',
            '_password' => 'test'
        ]);

        $this->client->submit($form);

        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);

        $this->client->followRedirect();

        $this->assertRouteSame('homepage');
    }

    public function testIfWrongPassword(): void
    {
        $crawler = $this->client->request(Request::METHOD_GET, $this->urlGenerator->generate('app_login'));

        $form = $crawler->filter('form[name=login]')->form([
            '_username' => 'ame',
            '_password' => 'password'
        ]);

        $this->client->submit($form);

        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);

        $this->client->followRedirect();

        $this->assertRouteSame('app_login');

        $this->assertSelectorTextContains('div.alert-danger', 'Invalid credentials.');
    }
}
