<?php

namespace App\Tests\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UserControllerTest extends WebTestCase
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

    /**
     * Test list users.
     */
    public function testListUsersNotConnected(): void
    {
        $this->client->request(Request::METHOD_GET, $this->urlGenerator->generate('user_list'));

        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);
        $this->assertResponseRedirects($this->urlGenerator->generate('app_login'));
    }

    public function testListUsersConnectedUserRole(): void
    {
        // User is an user
        $user = $this->em->getRepository(User::class)->findOneBy(['username' => 'user']);
        $this->client->loginUser($user);

        $this->client->request(Request::METHOD_GET, $this->urlGenerator->generate('user_list'));

        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testListUsersConnectedAdminRole(): void
    {
        // User is an admin
        $admin = $this->em->getRepository(User::class)->findOneBy(['username' => 'admin']);
        $this->client->loginUser($admin);

        $this->client->request(Request::METHOD_GET, $this->urlGenerator->generate('user_list'));

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    /**
     * Test create user.
     */
    public function testCreateUserNotConnected(): void
    {
        $this->client->request(Request::METHOD_GET, $this->urlGenerator->generate('user_create'));

        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);
        $this->assertResponseRedirects($this->urlGenerator->generate('app_login'));
    }

    public function testCreateUserConnectedUserRole(): void
    {
        // User is an user
        $user = $this->em->getRepository(User::class)->findOneBy(['username' => 'user']);
        $this->client->loginUser($user);

        $this->client->request(Request::METHOD_GET, $this->urlGenerator->generate('user_create'));

        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testCreateUserConnectedAdminRole(): void
    {
        $user = $this->client->getContainer()->get('doctrine')->getRepository(User::class)->findOneBy(
            ['username' => 'testCreate']
        );
        if ($user) {
            $this->client->getContainer()->get('doctrine')->getManager()->remove($user);
            $this->client->getContainer()->get('doctrine')->getManager()->flush();
        }

        // User is an admin
        $admin = $this->em->getRepository(User::class)->findOneBy(['username' => 'admin']);
        $this->client->loginUser($admin);

        $crawler = $this->client->request(Request::METHOD_GET, $this->urlGenerator->generate('user_create'));

        $form = $crawler->filter('form[name=user]')->form([
            'user[username]' => 'testCreate',
            'user[password][first]' => 'test',
            'user[password][second]' => 'test',
            'user[email]' => 'test@create.fr',
        ]);

        $this->client->submit($form);

        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);

        $this->client->followRedirect();

        $this->assertSelectorTextContains('div.alert-success', 'Superbe ! L\'utilisateur a bien été ajouté.');

        $this->assertRouteSame('user_list');
    }

    /**
     * Test edit user.
     */
    public function testEditUser(): void
    {
        $this->client->request(Request::METHOD_GET, $this->urlGenerator->generate('user_create'));

        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);
        $this->assertResponseRedirects($this->urlGenerator->generate('app_login'));
    }

    public function testEditUserConnectedUserRole(): void
    {
        // User is an user
        $user = $this->em->getRepository(User::class)->findOneBy(['username' => 'user']);
        $this->client->loginUser($user);

        $this->client->request(Request::METHOD_GET, $this->urlGenerator->generate('user_edit', ['id' => 1]));

        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testEditUserConnectedAdminRole(): void
    {
        $user = $this->client->getContainer()->get('doctrine')->getRepository(User::class)->findOneBy(
            ['username' => 'testEdit']
        );
        if ($user) {
            $this->client->getContainer()->get('doctrine')->getManager()->remove($user);
            $this->client->getContainer()->get('doctrine')->getManager()->flush();
        }
        // User is an admin
        $admin = $this->em->getRepository(User::class)->findOneBy(['username' => 'admin']);
        $this->client->loginUser($admin);

        $userId = $this->em->getRepository(User::class)->findOneBy(
            ['username' => 'testCreate']
        )->getId();

        $crawler = $this->client->request(Request::METHOD_GET, $this->urlGenerator->generate('user_edit', ['id' => $userId]));

        $form = $crawler->filter('form[name=user]')->form([
            'user[username]' => 'testEdit',
            'user[password][first]' => 'test',
            'user[password][second]' => 'test',
            'user[email]' => 'test@edit.fr',
        ]);

        $this->client->submit($form);

        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);

        $this->client->followRedirect();

        $this->assertSelectorTextContains('div.alert-success', 'Superbe ! L\'utilisateur a bien été modifié.');

        $this->assertRouteSame('user_list');
    }
}
