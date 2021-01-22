<?php


namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class UserControllerTest extends ControllerTest
{
    public function testCreateUserAccessibleToAdmin()
    {
        $this->createLogin('Admin');
        $this->client->request('GET', '/users/create');
        static::assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    public function testCreateUserNotAccessibleToUser()
    {
        $this->createLogin();
        $this->client->request('GET', '/users/create');
        static::assertResponseStatusCodeSame(Response::HTTP_FOUND);
        //        static::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    public function testCreateUserRestricted()
    {
        $this->client->request('GET', '/users/create');
        static::assertResponseStatusCodeSame(Response::HTTP_FOUND);
        //        static::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }
}
