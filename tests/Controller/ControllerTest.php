<?php


namespace App\Tests\Controller;

use App\DataFixtures\UserFixtures;
use App\Entity\User;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;


class ControllerTest extends WebTestCase
{
    use FixturesTrait;

    protected $client;

    public function setUp()
    {
        $this->client = static::createClient();
    }

    protected function CreateLogin($role='user')
    {
        $name = $role === 'Admin' ? 'Admin' : 'Camile';
        $users = $this->loadFixtures([UserFixtures::class]);
        /** @var User $user */
        $user = $users->getReferenceRepository()->getReferences()[$name];
        $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());

        $session = $this->client->getContainer()->get('session');
        $session->set('_security_main', serialize($token));
        $session->save();

        $cookie = new Cookie($session->getName(), $session->getId());
        $this->client->getCookieJar()->set($cookie);
    }


    public function testToAvoidWarningWhenTesting()
    {
        static::assertSame(1, 1);
    }
}
