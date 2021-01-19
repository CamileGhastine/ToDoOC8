<?php


namespace App\Tests\Controller;


use App\DataFixtures\UserFixtures;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class SecurityControllerTest extends WebTestCase
{
    use FixturesTrait;

    private $client;

    public function setUp()
    {
        $this->client = static::createClient();
    }

    public function testLoginPageStatusCode() {
        $this->client->request('GET', '/login');
        static::assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    public function testLoginFormDisplay(){
        $crawler = $this->client->request('GET', '/login');
        $this->assertSelectorExists('form');
        $this->assertSame(2, $crawler->filter('input')->count());
        $this->assertSame(1, $crawler->filter('label:contains("Nom d\'utilisateur :")')->count());
        $this->assertSame(1, $crawler->filter('label:contains("Mot de passe :")')->count());
        $this->assertSelectorTextSame('button', 'Se connecter');
    }

}