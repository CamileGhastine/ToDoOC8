<?php


namespace App\Tests\Repository;


use App\DataFixtures\TaskFixtures;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use App\Repository\TaskRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TaskRepositoryTest extends KernelTestCase
{
    Use FixturesTrait;

    public function getRepository() {
        self::bootKernel();
        $this->loadFixtures([TaskFixtures::class]);
        return self::$container->get(TaskRepository::class);
    }

    public function testCount(){
       $users= $this->getRepository()->count([]);
       $this->assertEquals(10, $users);
   }


}