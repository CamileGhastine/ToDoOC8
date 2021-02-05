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

        $form = $this->fillCreateForm($crawler);
        $this->client->submit($form);
        $this->client->followRedirect();

        $this->assertRouteSame('app_login', [], "Not app_login route after redirection");
        $this->assertSelectorExists('div.alert.alert-success', "No div alert-success");
    }

    public function testCreateUserUserSaveInDB()
    {
        $this->loadFixtures([UserFixtures::class]);
        $userRepository = $this->client->getContainer()->get('doctrine')->getRepository(User::class);
        $numberUsersBeforeSubmit = count($userRepository->findAll());

        $crawler = $this->client->request('GET', '/users/create');
        $form = $this->fillCreateForm($crawler);
        $this->client->submit($form);

        $NumberUsersAfterSubmit = count($userRepository->findAll());
        $newUser = $userRepository->findOneById(3);

        $this->assertSame($numberUsersBeforeSubmit + 1, $NumberUsersAfterSubmit, "No New User in DB");
        $this->assertSame('user', $newUser->getUsername(), "Good user register in DB");
    }

    public function testCreateUserFormConstraintsWhenFormIsSubmittedWithNoValidUsername()
    {
        $crawler = $this->client->request('GET', '/users/create');

        $form = $this->fillCreateForm($crawler);
        $form['user[username]'] = 'h';
        $this->client->submit($form);

        $this->assertSelectorExists('div.form-group.has-error');
    }

    public function testCreateUserFormConstraintsWhenFormIsSubmittedWithNoValidEmail()
    {
        $crawler = $this->client->request('GET', '/users/create');

        $form = $this->fillCreateForm($crawler);
        $form['user[email]'] = 'unvalid email';
        $this->client->submit($form);

        $this->assertSelectorExists('div.form-group.has-error');
    }

    public function testCreateUserFormConstraintsWhenFormIsSubmittedWithNoValidPassword()
    {
        $crawler = $this->client->request('GET', '/users/create');

        $form = $this->fillCreateForm($crawler);
        $form['user[password][first]'] = 'paswword';
        $this->client->submit($form);

        $this->assertSelectorExists('div.form-group.has-error');
    }

    public function testCreateUserFormConstraintsWhenFormIsSubmittedWithDifferentPassword()
    {
        $crawler = $this->client->request('GET', '/users/create');

        $form = $this->fillCreateForm($crawler);
        $form['user[password][first]'] = 'Password2021';
        $form['user[password][first]'] = 'Password2022';
        $this->client->submit($form);

        $this->assertSelectorExists('div.form-group.has-error');
    }

    public function testCreateUserFormConstraintsWhenFormIsSubmittedWithWrongToken()
    {
        $crawler = $this->client->request('GET', '/users/create');

        $form = $this->fillCreateForm($crawler);
        $form['user[_token]'] = 'wrongToken';
        $this->client->submit($form);

        $this->assertSelectorExists('div.alert.alert-danger');
    }

    public function testCreateUserFormConstraintsWhenFormIsSubmittedWithNotUniqueUsername()
    {
        $crawler = $this->client->request('GET', '/users/create');

        $form = $this->fillCreateForm($crawler);
        $form['user[username]'] = 'Camile';
        $this->client->submit($form);

        $this->assertSelectorExists('div.form-group.has-error');
    }

    public function testCreateUserFormConstraintsWhenFormIsSubmittedWithNotUniqueEmail()
    {
        $crawler = $this->client->request('GET', '/users/create');

        $form = $this->fillCreateForm($crawler);
        $form['user[email]'] = 'Camile@todoco.fr';
        $this->client->submit($form);

        $this->assertSelectorExists('div.form-group.has-error');
    }

    public function testEditUserRoute()
    {
        $this->createLogin('Admin');
        $this->client->request('GET', '/users/1/edit');

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    public function testEditUserNotAccessibleToUser()
    {
        $this->createLogin();
        $this->client->request('GET', '/users/1/edit');
        $this->client->followRedirect();

        $this->assertRouteSame('homepage');
    }

    public function testEditUserDisplayForm()
    {
        $this->createLogin('Admin');
        $crawler = $this->client->request('GET', '/users/1/edit');

        $this->assertSelectorExists('div.userForm', 'No div with class "userForm"');
        $this->assertSame(5, $crawler->filter('input')->count(), 'Should have 5 input include token');
        $this->assertSelectorExists('button:contains("Modifier")', 'No submit button "Modifier"');
        $this->assertInputValueSame('user[username]', 'Admin');
        $this->assertInputValueSame('user[email]', 'Admin@todoco.fr');
        $this->assertInputValueSame('user[role]', 'ROLE_USER');
    }

    public function testEditUserRedirectFormValid()
    {
        $this->createLogin('Admin');
        $crawler = $this->client->request('GET', '/users/1/edit');

        $form = $crawler->selectButton('Modifier')->form();
        $form['user[username]'] = 'usernameEdited';
        $this->client->submit($form);
        $this->client->followRedirect();

        $this->assertRouteSame('user_list', [], "Not app_login route after redirection");
        $this->assertSelectorExists('div.alert.alert-success', "No div alert-success");
    }

    public function testEditUserUserSaveInDB()
    {
        $this->createLogin('Admin');

        $userRepository = $this->client->getContainer()->get('doctrine')->getRepository(User::class);
        $numberUsersBeforeSubmit = count($userRepository->findAll());

        $crawler = $this->client->request('GET', '/users/2/edit');
        $form = $crawler->selectButton('Modifier')->form();
        $form['user[username]'] = 'usernameEdited';
        $form['user[email]'] ='emailedited@todoco.fr';
        $form['user[role]'] ='ROLE_ADMIN';
        $this->client->submit($form);

        $NumberUsersAfterSubmit = count($userRepository->findAll());
        $newUser = $userRepository->findOneById(2);

        $this->assertSame($numberUsersBeforeSubmit, $NumberUsersAfterSubmit, "number of User in DB have change");
        $this->assertSame('usernameEdited', $newUser->getUsername(), "Good username edited in DB");
        $this->assertSame('emailedited@todoco.fr', $newUser->getEmail(), "Good email edited in DB");
        $this->assertSame('ROLE_ADMIN', $newUser->getRole(), "Good role edited in DB");
    }

    public function testEditUserFormConstraintsWhenFormIsSubmittedWithNoValidUsername()
    {
        $this->createLogin('Admin');
        $crawler = $this->client->request('GET', '/users/1/edit');

        $form = $crawler->selectButton('Modifier')->form();
        $form['user[username]'] = 'u';
        $this->client->submit($form);

        $this->assertSelectorExists('div.form-group.has-error');
    }

    public function testEditUserFormConstraintsWhenFormIsSubmittedWithNoValidEmail()
    {
        $this->createLogin('Admin');
        $crawler = $this->client->request('GET', '/users/1/edit');

        $form = $crawler->selectButton('Modifier')->form();
        $form['user[email]'] = 'unvalid email';
        $this->client->submit($form);

        $this->assertSelectorExists('div.form-group.has-error');
    }

    public function testEditUserFormConstraintsWhenFormIsSubmittedWithWrongToken()
    {
        $this->createLogin('Admin');
        $crawler = $this->client->request('GET', '/users/1/edit');

        $form = $crawler->selectButton('Modifier')->form();
        $form['user[_token]'] = 'wrongToken';
        $this->client->submit($form);

        $this->assertSelectorExists('div.alert.alert-danger');
    }

    public function testEditUserFormConstraintsWhenFormIsSubmittedWithNotUniqueUsername()
    {
        $this->createLogin('Admin');
        $crawler = $this->client->request('GET', '/users/1/edit');

        $form = $crawler->selectButton('Modifier')->form();
        $form['user[username]'] = 'Camile';
        $this->client->submit($form);

        $this->assertSelectorExists('div.form-group.has-error');
    }

    public function testEditUserFormConstraintsWhenFormIsSubmittedWithNotUniqueEmail()
    {
        $this->createLogin('Admin');
        $crawler = $this->client->request('GET', '/users/1/edit');

        $form = $crawler->selectButton('Modifier')->form();
        $form['user[email]'] = 'Camile@todoco.fr';
        $this->client->submit($form);

        $this->assertSelectorExists('div.form-group.has-error');
    }

    private function fillCreateForm($crawler)
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
