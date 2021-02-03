<?php


namespace App\Tests\Controller;

use App\DataFixtures\UserFixtures;
use App\Entity\User;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Prophecy\Argument\Token\AnyValuesToken;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Csrf\TokenStorage\SessionTokenStorage;
use Symfony\Component\Security\Csrf\TokenGenerator\UriSafeTokenGenerator;

class UserControllerTest extends ControllerTest
{
    use FixturesTrait;

    public function testListUserAccessibleToAdmin()
    {
        $this->createLogin('Admin');
        $this->client->request('GET', '/users');
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }

//    public function testListUserNotAccessibleToUser()
//    {
//        $this->createLogin();
//        $this->client->request('GET', '/users');
//        $this->assertResponseRedirects('/login', Response::HTTP_FOUND);
//    }

    public function testListUserDisplayAllUsersWithEditButton()
    {
        $this->createLogin('Admin');
        $crawler = $this->client->request('GET', '/users');

        $this->assertSame(2, $crawler->filter('tr.user')->count(), 'All user not display ');
        $this->assertSame(2, $crawler->filter('tr.user>td>a.btn')->count(), 'Edit button missing');
    }


    public function testCreateUserAccessibleToAnonymous()
    {
        $this->client->request('GET', '/users/create');
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    public function testDisplayUserFormWhenFormIsNotSubmitted()
    {
        $crawler = $this->client->request('GET', '/users/create');

        $this->assertSelectorExists('div.userForm', 'No div with class "userForm"');
        $this->assertSame(5, $crawler->filter('input')->count(), 'Should have 5 input');
        $this->assertSelectorExists('button:contains("Ajouter")', 'No submit button "Ajouter"');
    }

//    public function testRedirectToLoginWhenFormIsSubmittedAndValid()
//    {
//        $crawler = $this->client->request('GET', '/users/create');
//
//        $form = $this->submitForm($crawler);
//        $this->client->submit($form);
//
//        $this->assertResponseRedirects('login');
//        $crawler = $this->client->followRedirect();
//        $this->assertSame(1, $crawler->filter('div.alert.alert-success')->count());
//    }


    public function testFormConstraintsWhenFormIsSubmittedWithNoValidUsername()
    {
        $crawler = $this->client->request('GET', '/users/create');

        $form = $this->submitForm($crawler);
        $form['user[username]'] = 'h';
        $this->client->submit($form);

        $this->assertSelectorExists('div.form-group.has-error');
    }

    public function testFormConstraintsWhenFormIsSubmittedWithNoValidEmail()
    {
        $crawler = $this->client->request('GET', '/users/create');

        $form = $this->submitForm($crawler);
        $form['user[email]'] = 'unvalid email';
        $this->client->submit($form);

        $this->assertSelectorExists('div.form-group.has-error');
    }

    public function testFormConstraintsWhenFormIsSubmittedWithNoValidPassword()
    {
        $crawler = $this->client->request('GET', '/users/create');

        $form = $this->submitForm($crawler);
        $form['user[password][first]'] = 'paswword';
        $this->client->submit($form);

        $this->assertSelectorExists('div.form-group.has-error');
    }

    public function testFormConstraintsWhenFormIsSubmittedWithDifferentdPassword()
    {
        $crawler = $this->client->request('GET', '/users/create');

        $form = $this->submitForm($crawler);
        $form['user[password][first]'] = 'Password2021';
        $form['user[password][first]'] = 'Password2022';
        $this->client->submit($form);

        $this->assertSelectorExists('div.form-group.has-error');
    }

    public function testFormConstraintsWhenFormIsSubmittedWithWrongToken()
    {
        $crawler = $this->client->request('GET', '/users/create');

        $form = $this->submitForm($crawler);
        $form['user[_token]'] = 'wrongToken';
        $this->client->submit($form);

        $this->assertSelectorExists('div.alert.alert-danger');
    }

    public function testFormConstraintsWhenFormIsSubmittedWithNotUniqueUsername()
    {
        $crawler = $this->client->request('GET', '/users/create');

        $form = $this->submitForm($crawler);
        $form['user[username]'] = 'Camile';
        $this->client->submit($form);

        $this->assertSelectorExists('div.form-group.has-error');
    }

    public function testFormConstraintsWhenFormIsSubmittedWithNotUniqueEmail()
    {
        $crawler = $this->client->request('GET', '/users/create');

        $form = $this->submitForm($crawler);
        $form['user[email]'] = 'Camile@todoco.fr';
        $this->client->submit($form);

        $this->assertSelectorExists('div.form-group.has-error');
    }

    public function testUserSaveInDB()
    {
        $usersBeforeSubmit= $this->client->getContainer()->get('doctrine')->getRepository(User::class)->findAll();

        $crawler = $this->client->request('GET', '/users/create');
        $form = $this->submitForm($crawler);
        $this->client->submit($form);

        $usersAfterSubmit = $this->client->getContainer()->get('doctrine')->getRepository(User::class)->findAll();
        $this->assertSame(count($usersBeforeSubmit) + 1, count($usersAfterSubmit));
    }


    private function submitForm($crawler)
    {
        $token = $this->client->getContainer()->get('session')
            ->get('_csrf/user');
        $username = 'username'.rand(0, 1000000);

        $form = $crawler->selectButton('Ajouter')->form();
        $form['user[username]'] = $username;
        $form['user[email]'] = $username.'@todoco.fr';
        $form['user[password][first]'] = 'Password1';
        $form['user[password][second]'] = 'Password1';
        $form['user[_token]'] = $token;

        return $form;
    }
}
