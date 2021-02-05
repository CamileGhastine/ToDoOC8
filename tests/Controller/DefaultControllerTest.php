<?php


namespace App\Tests\Controller;

use Symfony\Component\HttpFoundation\Response;

class DefaultControllerTest extends ControllerTest
{
    public function testHomePageIsRestricted()
    {
        $this->client->request('GET', '/');

        $this->assertResponseRedirects('/login', Response::HTTP_FOUND);
    }

    public function testHomePageAccessibleToUser()
    {
        $this->createLogin();
        $this->client->request('GET', '/');
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    public function testHomePageView()
    {
        $this->createLogin();
        $crawler = $this->client->request('GET', '/');

        $this->assertSame(3, $crawler->filter('div.home>div>a.btn')->count());
    }
}
