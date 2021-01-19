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

//    public function testHomePageRedirectToLogin()
//    {
//        $this->client->request('GET', '/');
//        $crawler = $this->client->followRedirect();
//
//        static::assertSelectorExists('form', 'No <Form>');
//        static::assertSame(2, $crawler->filter('input')->count(), 'Count <input> != 2');
//        static::assertSame(1, $crawler->filter('label:contains("Nom d\'utilisateur :")')->count(), 'No <label> for username');
//        static::assertSame(1, $crawler->filter('label:contains("Mot de passe :")')->count(), 'No <label> for password');
//        static::assertSelectorTextSame('button', 'Se connecter', 'No button <submit>');
//
//        static::assertResponseRedirects('/login');
//    }
//
//    public function testCreateTaskForm(){
//
//        $csrfToken = $this->client->getContainer()->get('security.csrf.token_manager');
//
//        $this->client->request('POST', '/tasks/create', [
//            "task[title]" => "nouveau titre",
//            "task[content]" => "nouveau contenu",
//            "task[_token]" => $csrfToken
//        ]);
//        $this->client->followRedirect();
//
//        static::assertSelectorTextContains('.alert', 'superbe');
//
//    }



}