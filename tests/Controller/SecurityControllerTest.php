<?php


namespace App\Tests\Controller;

use App\DataFixtures\UserFixtures;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class SecurityControllerTest extends ControllerTest
{
    public function testLoginPageStatusCode()
    {
        $this->client->request('GET', '/login');
        static::assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    public function testLoginWhenUserExists()
    {
        $this->createLogin();
        $this->client->request('GET', 'login');
        $this->client->followRedirect();
        static::assertSelectorTextContains('h1', 'Bienvenue sur Todo List, l\'application vous permettant de gérer l\'ensemble de vos tâches sans effort !');

//        echo $this->client->getResponse()->getContent();
//        static::assertResponseRedirects('homepage');
    }

    public function testLoginFormDisplay()
    {
        $crawler = $this->client->request('GET', '/login');
        static::assertSelectorExists('form', 'No <Form>');
        static::assertSame(3, $crawler->filter('input')->count(), 'Count <input> != 2');
        static::assertSame(1, $crawler->filter('label:contains("Nom d\'utilisateur :")')->count(), 'No <label> for username');
        static::assertSame(1, $crawler->filter('label:contains("Mot de passe :")')->count(), 'No <label> for password');
        static::assertSelectorTextSame('button', 'Se connecter', 'No button <submit> Se connecter');
    }

    public function testLoginFormSubmitSuccess()
    {
        $this->loadFixtures([UserFixtures::class]);
//        $this->loadFixtureFiles([__DIR__ . '/userFixture.yaml']);
        $this->submitForm('Camile', 'Camile1');

        static::assertSelectorTextContains('h1', 'Bienvenue sur Todo List, l\'application vous permettant de gérer l\'ensemble de vos tâches sans effort !');
    }

    public function testLoginFormSubmitFailure()
    {
        $this->submitForm('Camile', 'wrong password');

        static::assertSelectorTextContains('.alert.alert-danger', 'Identifiants invalides.', 'No class alert and alert-danger');
        static::assertInputValueSame('_username', 'Camile', 'No expected input value');
        static::assertSelectorTextContains('h4', 'Connectez-vous pour pouvoir utiliser toutes les fonctionnalités du site.', 'No come Back to login');
    }

    private function submitForm($username, $password)
    {
        $crawler = $this->client->request('GET', '/login');
        $form = $crawler->selectButton('Se connecter')->form([
            '_username' => $username,
            '_password' => $password
        ]);
        $this->client->submit($form);

        $this->client->followRedirect();
    }

    public function testLogoutSessionClose()
    {
        $this->createlogin();
        // user registered in session
        static::assertSame(true, (bool)$this->client->getContainer()->get('session')->get('_security_main'));

        $this->client->request('GET', '/logout');
        // No user registered in session
        static::assertSame(false, (bool)$this->client->getContainer()->get('session')->get('_security_main'));
    }

    public function testLogoutRedirectToLoginPage()
    {
        $this->client->followRedirects();
        $this->createlogin();
        $this->client->request('GET', '/logout');

        static::assertSelectorTextContains('h4', 'Connectez-vous pour pouvoir utiliser toutes les fonctionnalités du site.');
    }
}
