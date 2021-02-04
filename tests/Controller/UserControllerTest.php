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

    // test the fixtures user has not been changed. Otherwise some tests won't be OK
    public function testFixturesUserExactForTests()
    {
        $this->assertSame(1, 1);
        $this->loadFixtures([UserFixtures::class]);
        $UserRepository = $this->getContainer()->get('doctrine')->getRepository('App:User');

        $admin = $UserRepository->findOneById(1);
        $this->assertSame('Admin', $admin->getUsername(), "Not good username for fixture test");
        $this->assertSame('ROLE_ADMIN', $admin->getRole(), "Not good role for user fixtures test");

        $user = $UserRepository->findOneById(2);
        $this->assertSame('Camile', $user->getUsername(), "Not good username for fixture test");
        $this->assertSame('ROLE_USER', $user->getRole(), "Not good role for user fixtures test");
    }

    public function testListUserAccessibleToAdmin()
    {
        $this->createLogin('Admin');
        $this->client->request('GET', '/users');

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    public function testListUserNotAccessibleToUser()
    {
        $this->client->followRedirects();
        $this->createLogin();
        $this->client->request('GET', '/users');

        $this->assertRouteSame('homepage');
    }

    public function testListUserDisplay()
    {
        $this->createLogin('Admin');
        $crawler = $this->client->request('GET', '/users');

        $this->assertSelectorTextContains('tr>td', 'Admin', "User Admin not listed");
        $this->assertSelectorExists('tr>td:contains("Camile")', "User Camile not listed");
        $this->assertSame(2, $crawler->filter('tr.user>td>a.btn')->count(), 'Edit button missing');
    }


    public function testCreateUserRoute()
    {
        $this->client->request('GET', '/users/create');
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    public function testCreateUserDisplayForm()
    {
        $crawler = $this->client->request('GET', '/users/create');

        $this->assertSelectorExists('div.userForm', 'No div with class "userForm"');
        $this->assertSame(5, $crawler->filter('input')->count(), 'Should have 5 input');
        $this->assertSelectorExists('button:contains("Ajouter")', 'No submit button "Ajouter"');
    }

    public function testCreateUserRedirectFormValid()
    {
        $this->loadFixtures([UserFixtures::class]);
        $crawler = $this->client->request('GET', '/users/create');

        $form = $this->submitForm($crawler);
        $this->client->submit($form);
        $this->client->followRedirect();

        $this->assertRouteSame('app_login',[], "Not app_login route after redirection");
        $this->assertSelectorExists('div.alert.alert-success', "No div alert-success");
    }

    public function testUserSaveInDB()
    {
        $this->loadFixtures([UserFixtures::class]);
        $userRepository = $this->client->getContainer()->get('doctrine')->getRepository(User::class);
        $numberUsersBeforeSubmit = count($userRepository->findAll());

        $crawler = $this->client->request('GET', '/users/create');
        $form = $this->submitForm($crawler);
        $this->client->submit($form);

        $NumberUsersAfterSubmit = count($userRepository->findAll());
        $newUser = $userRepository->findOneById(3);

        $this->assertSame($numberUsersBeforeSubmit + 1, $NumberUsersAfterSubmit, "No New User in DB");
        $this->assertSame('user', $newUser->getUsername(), "Good user register in DB");
    }

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

    public function testFormConstraintsWhenFormIsSubmittedWithDifferentPassword()
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


    private function submitForm($crawler)
    {
        $token = $this->client->getContainer()->get('session')
            ->get('_csrf/user');
        $username = 'user';

        $form = $crawler->selectButton('Ajouter')->form();
        $form['user[username]'] = $username;
        $form['user[email]'] = $username.'@todoco.fr';
        $form['user[password][first]'] = 'Password1';
        $form['user[password][second]'] = 'Password1';
        $form['user[_token]'] = $token;

        return $form;
    }
}
