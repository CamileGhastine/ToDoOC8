<?php

namespace App\Tests\Controller;

use App\DataFixtures\TaskFixtures;
use App\DataFixtures\UserFixtures;
use App\Entity\Task;
use App\Repository\TaskRepository;
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

    public function testTaskIsDoneListInaccessibleToAnonymous()
    {
        $this->client->request('GET', '/tasks/done');
        $this->assertResponseRedirects('/login');
    }

    public function testA()
    {
        $this->createLogin();
        $crawler = $this->client->request('GET', '/tasks/done');
        $this->assertEquals(
            count($this->getContainer()->get('doctrine')->getRepository('App:Task')->findTasksIsDone()),
            $crawler->filter('.task')->count()
        );
    }
}
