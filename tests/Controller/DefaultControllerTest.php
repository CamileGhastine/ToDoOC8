<?php


namespace App\Tests\Controller;


use Symfony\Component\HttpFoundation\Response;

class DefaultControllerTest extends ControllerTest
{
    public function testHomePageIsRestricted()
    {
        $this->client->request('GET', '/');

        static::assertResponseRedirects('/login', Response::HTTP_FOUND);
    }

    public function testHomePageAccessibleToUser()
    {
        $this->createLogin();
        $this->client->request('GET', '/');
        static::assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    public function testHomePageView(){
        $this->createLogin();
        $crawler = $this->client->request('GET', '/');

        static::assertSame(3, $crawler->filter('div.home>a.btn')->count());
    }
}