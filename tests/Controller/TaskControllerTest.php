<?php

namespace App\Tests\Controller;


use App\DataFixtures\UserFixtures;
use App\Entity\User;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;


class TaskControllerTest extends ControllerTest
{

    public function testHomePageIsRestricted()
    {
        $this->client->request('GET', '/');
        static::assertResponseStatusCodeSame(Response::HTTP_FOUND);
        //        static::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    public function testHomePageAccessibleToUser()
    {
        $this->createLogin();
        $this->client->request('GET', '/');
        static::assertResponseStatusCodeSame(200);
    }

}