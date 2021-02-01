<?php


namespace App\Tests\Controller;

use App\DataFixtures\TaskFixtures;
use App\DataFixtures\UserFixtures;
use App\Entity\User;
use App\Repository\TaskRepository;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;


class ControllerTest extends WebTestCase
{
    use FixturesTrait;

    protected $client;

    public function setUp(): void
    {
        $this->client = static::createClient();
    }

    protected function CreateLogin($role='user')
    {
        $name = $role === 'Admin' ? 'Admin' : 'Camile';
        $users = $this->loadFixtures([UserFixtures::class, TaskFixtures::class]);
        $user = $users->getReferenceRepository()->getReferences()[$name];

        $this->client->loginUser($user);
    }

    public function testToAvoidWarningWhenTesting()
    {
        static::assertSame(1, 1);
    }
}
