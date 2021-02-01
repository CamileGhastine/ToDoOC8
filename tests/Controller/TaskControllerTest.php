<?php

namespace App\Tests\Controller;

use App\DataFixtures\TaskFixtures;
use App\DataFixtures\UserFixtures;
use App\Entity\Task;
use Liip\TestFixturesBundle\Test\FixturesTrait;


class TaskControllerTest extends ControllerTest
{
    use FixturesTrait;

    public function testTaskListInaccessibleToAnonymous()
    {
        $this->client->request('GET', '/tasks');
        $this->assertResponseRedirects('/login');
    }

    public function testTaskListDisplay()
    {
        $this->createLogin();
        $crawler = $this->client->request('GET', '/tasks');
        $this->assertEquals(
            count($this->getContainer()->get('doctrine')->getRepository('App:Task')->findAll()),
            $crawler->filter('.task')->count()
        );
    }


}
