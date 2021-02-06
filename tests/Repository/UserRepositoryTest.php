<?php

namespace App\Tests\Repository;

use App\DataFixtures\UserFixtures;
use App\Repository\UserRepository;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class UserRepositoryTest extends KernelTestCase
{
    use FixturesTrait;

    public function getRepository()
    {
        self::bootKernel();
        $this->loadFixtures([UserFixtures::class]);
        return self::$container->get(UserRepository::class);
    }

    public function testCount()
    {
        $users = $this->getRepository()->count([]);
        $this->assertEquals(2, $users);
    }
}
