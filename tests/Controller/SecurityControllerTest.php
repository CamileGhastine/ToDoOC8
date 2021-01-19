<?php


namespace App\Tests\Controller;


use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class SecurityControllerTest extends WebTestCase
{
    private $client;

    public function setUp()
    {
        $this->client = static::createClient();
    }

    public function testLoginPageStatusCode() {
        $this->client->request('GET', '/login');
        static::assertResponseStatusCodeSame(Response::HTTP_OK);
    }
}