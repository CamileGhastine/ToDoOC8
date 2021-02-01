<?php

namespace App\Tests\Controller;

use App\DataFixtures\TaskFixtures;
use App\DataFixtures\UserFixtures;
use App\Entity\Task;
use App\Repository\TaskRepository;
use DateTime;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Component\HttpFoundation\Response;


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

    public function testTaskIsDoneListDisplay()
    {
        $this->createLogin();
        $crawler = $this->client->request('GET', '/tasks/done');

        $this->assertEquals(
            count($this->getContainer()->get('doctrine')->getRepository('App:Task')->findTasksIsDone()),
            $crawler->filter('.task')->count()
        );
    }

    public function testTaskCreateInaccessibleToAnonymous()
    {
        $this->client->request('GET', '/tasks/create');

        $this->assertResponseRedirects('/login');
    }

    public function testTaskCreateFormDisplay()
    {
        $this->createLogin();
        $crawler = $this->client->request('GET', '/tasks/create');

        $this->assertSelectorExists(
            'div.taskCreateForm',
            'No div with class "taskCreateForm"'
        );
        $this->assertSame(
            2,
            $crawler->filter('input')->count(),
            'Should have 3 input (include hidden token)'
        );
        $this->assertSame(
            1,
            $crawler->filter('textarea')->count(),
            'Should have textarea)'
        );
        $this->assertSelectorExists(
            'button:contains("Ajouter")',
            'No submit button "Ajouter"'
        );
    }

    public function testTaskCreateRedirectionWhenFormIsSubmitted()
    {
        $this->createLogin();
        $crawler = $this->client->request('GET', '/tasks/create');

        $form = $this->fillForm($crawler);
        $this->client->submit($form);

        $this->assertResponseRedirects('/tasks');
    }

    public function testTaskCreateRegisterInDB()
    {
        $this->createLogin();
        $crawler = $this->client->request('GET', '/tasks/create');

        $taskRepository = $this->getContainer()
            ->get('doctrine')
            ->getRepository('App:Task');
        $countTasksBefore = count($taskRepository->findAll());

        $form = $this->fillForm($crawler);
        $this->client->submit($form);

        $this->assertEquals(
            count($taskRepository->findAll()),
            $countTasksBefore +1,
            "Task not register in DB"
        );
        $this->assertSame(
            'A new title task',
            $taskRepository->findBy([], ['id' => 'desc'],1)[0]->getTitle(),
            "Title not well register in DB"
        );
        $this->assertSame(
            'A new content task',
            $taskRepository->findBy([], ['id' => 'desc'],1)[0]->getContent(),
            "Content not well register in DB"
        );
        $this->assertResponseRedirects(
            '/tasks',
            Response::HTTP_FOUND,
            "No redirection to task_list"
        );

        $this->client->followRedirect();
        $this->assertSelectorExists('.alert.alert-success', "No flash success message");
    }

    public function testTaskCreateFormNotValidTitleBlank()
    {
        $this->createLogin();
        $crawler = $this->client->request('GET', '/tasks/create');

        $form = $this->fillForm($crawler);
        $form['task[title]'] = '';
        $this->client->submit($form);

        $this->assertSelectorExists('li:contains("Vous devez saisir un titre")');
    }

    public function testTaskCreateFormNotValidTitleShort()
    {
        $this->createLogin();
        $crawler = $this->client->request('GET', '/tasks/create');

        $form = $this->fillForm($crawler);
        $form['task[title]'] = 'a';
        $this->client->submit($form);

        $this->assertSelectorExists('li:contains("Le titre est trop court")');
    }

    public function testTaskCreateFormNotValidTitleLong()
    {
        $this->createLogin();
        $crawler = $this->client->request('GET', '/tasks/create');

        $title='';
        for($i=0; $i<51;$i++) {
            $title .='a';
        }

        $form = $this->fillForm($crawler);
        $form['task[title]'] = $title;
        $this->client->submit($form);

        $this->assertSelectorExists('li:contains("Le titre est trop long")');
    }

    public function testTaskCreateFormNotValidContentBlank()
    {
        $this->createLogin();
        $crawler = $this->client->request('GET', '/tasks/create');

        $form = $this->fillForm($crawler);
        $form['task[content]'] = '';
        $this->client->submit($form);

        $this->assertSelectorExists('li:contains("Vous devez saisir un contenu")');
    }

    public function testTaskCreateIsDoneSetToFalse()
    {
        $this->createLogin();
        $crawler = $this->client->request('GET', '/tasks/create');
        $form = $this->fillForm($crawler);
        $this->client->submit($form);

        $taskRepository = $this->getContainer()->get('doctrine')->getRepository('App:Task');

        $this->assertSame(
            false,
            $taskRepository->findOneBy(['title' => 'A new title task'])->isDone());
    }

    public function testTaskCreateCreatedAtSetCorrectly()
    {
        $this->createLogin();
        $crawler = $this->client->request('GET', '/tasks/create');
        $form = $this->fillForm($crawler);
        $this->client->submit($form);

        $taskRepository = $this->getContainer()->get('doctrine')->getRepository('App:Task');
        $expectedTimestamp = (int)(((new DateTime())->getTimestamp())/10);
        $createdAtTimestamp = (int)(
            ($taskRepository->findOneBy(['title' => 'A new title task'])
                ->getCreatedAt()->getTimestamp())/10);

        $this->assertSame($expectedTimestamp, $createdAtTimestamp);
    }

    public function testTaskCreateSetUser()
    {
        $user = $this->createLogin();
        $crawler = $this->client->request('GET', '/tasks/create');
        $form = $this->fillForm($crawler);
        $this->client->submit($form);

        $taskRepository = $this->getContainer()->get('doctrine')->getRepository('App:Task');

        $this->assertSame(
            $user->getId(),
            $taskRepository->findOneBy(['title' => 'A new title task'])->getUser()->getId(),
            "User not set Correctly"
        );
    }

    public function testTaskEditInaccessibleToAnonymous()
    {
        $this->client->request('GET', '/tasks/1/edit');

        $this->assertResponseRedirects('/login');
    }

    public function testTaskEditFormDisplay()
    {
        $this->createLogin();
        $this->client->request('GET', '/tasks/1/edit');

        $taskRepostory = $this->getContainer()->get('doctrine')->getRepository('App:Task');
        $task = $taskRepostory->findOneBy(['id' => 1]);

        $this->assertSelectorExists(
            'div.taskEditForm',
            'No div with class "taskEditForm"'
        );
        $this->assertInputValueSame(
            'task[title]',
            $task->getTitle(),
            'title display does not match'
        );
        $this->assertSelectorTextContains(
            'textarea',
            $task->getContent(),
            'textarea does not match'
        );
        $this->assertSelectorExists(
            'button:contains("Modifier")',
            'No submit button "Ajouter"'
        );
    }

    public function testTaskEditRedirectionWhenFormIsSubmitted()
    {
        $this->createLogin();
        $crawler = $this->client->request('GET', '/tasks/1/edit');

        $form = $crawler->selectButton('Modifier')->form();
        $form['task[title]'] = 'An edit title task';
        $this->client->submit($form);

        $this->assertResponseRedirects('/tasks');
    }

    public function testTaskEditRegisterCorrectlyInDB()
    {
        $this->createLogin();
        $crawler = $this->client->request('GET', '/tasks/1/edit');

        $taskRepository = $this->getContainer()
            ->get('doctrine')
            ->getRepository('App:Task');
        $countTasksBefore = count($taskRepository->findAll());
        $taskBeforeEdition = $taskRepository->findOneBy(['id' => 1]);

        $form = $crawler->selectButton('Modifier')->form();
        $form['task[title]'] = 'An edit title task';
        $this->client->submit($form);

        $this->assertEquals(
            count($taskRepository->findAll()),
            $countTasksBefore,
            "Task Created or deleted in DB"
        );
        $this->assertSame(
            'An edit title task',
            $taskRepository->findOneBy(['id' =>1])->getTitle(),
            "Title edition problem"
        );
        $this->assertSame(
            $taskBeforeEdition->getContent(),
            $taskRepository->findOneBy(['id' => 1])->getContent(),
            "Content edition problem"
        );
        $this->assertResponseRedirects(
            '/tasks',
            Response::HTTP_FOUND,
            "No redirection to task_list"
        );

        $this->client->followRedirect();
        $this->assertSelectorExists('.alert.alert-success', "No flash success message");
    }

    public function testTaskEditFormNotValid()
    {
        $this->createLogin();
        $crawler = $this->client->request('GET', '/tasks/1/edit');

        $form = $crawler->selectButton('Modifier')->form();
        $form['task[title]'] = '';
        $this->client->submit($form);

        $this->assertSelectorExists('li:contains("Vous devez saisir un titre")');

    }

    private function fillForm($crawler)
    {
        $token = $this->client->getContainer()->get('session')->get('_csrf/task');
        $form = $crawler->selectButton('Ajouter')->form();
        $form['task[title]'] = 'A new title task';
        $form['task[content]'] = 'A new content task';
        $form['task[_token]'] = $token;

        return $form;
    }


}
