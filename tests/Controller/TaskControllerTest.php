<?php

namespace App\Tests\Controller;

use App\DataFixtures\TaskFixtures;
use DateTime;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Component\HttpFoundation\Response;

class TaskControllerTest extends ControllerTest
{
    use FixturesTrait;

    // test the fixtures task has not been changed. Otherwise some tests won't be OK
    public function testFixturesTaskExactForTests()
    {
        $this->assertSame(1, 1);
        $this->loadFixtures([TaskFixtures::class]);
        $UserRepository = $this->getContainer()->get('doctrine')->getRepository('App:Task');

        for ($i = 1; $i <= 3; $i++) {
            $expected = $i < 3 ? $i : null;
            $task = $UserRepository->findOneById($i);

            $this->assertSame($expected, $i < 3
                ? $task->getUser()->getId()
                : $task->getUser(), "Not good user for task fixtures test");
        }
    }

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
            $countTasksBefore + 1,
            "Task not register in DB"
        );
        $this->assertSame(
            'A new title task',
            $taskRepository->findBy([], ['id' => 'desc'], 1)[0]->getTitle(),
            "Title not well register in DB"
        );
        $this->assertSame(
            'A new content task',
            $taskRepository->findBy([], ['id' => 'desc'], 1)[0]->getContent(),
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

        $title = '';
        for ($i = 0; $i < 51; $i++) {
            $title .= 'a';
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
            $taskRepository->findOneBy(['title' => 'A new title task'])->isDone()
        );
    }

    //This test is not conform. It can fail 1 ouf of 100
    public function testTaskCreateCreatedAtSetCorrectly()
    {
        $this->createLogin();
        $crawler = $this->client->request('GET', '/tasks/create');
        $form = $this->fillForm($crawler);
        $this->client->submit($form);

        $taskRepository = $this->getContainer()->get('doctrine')->getRepository('App:Task');
        $expectedTimestamp = (int)(((new DateTime())->getTimestamp()) / 100);
        $createdAtTimestamp = (int)(
            ($taskRepository->findOneBy(['title' => 'A new title task'])
                ->getCreatedAt()->getTimestamp()) / 100
        );

        $this->assertSame($expectedTimestamp, $createdAtTimestamp, "BE CARFUL This test can fail 1 out of 100");
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
            $taskRepository->findOneBy(['id' => 1])->getTitle(),
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

    public function testTaskToggleInaccessibleToAnonymous()
    {
        $this->client->request('GET', '/tasks/1/toggle');

        $this->assertResponseRedirects('/login');
    }

    public function testTaskToggleChangeIsDonePOST()
    {
        $this->createLogin();

        $taskRepository = $this->getContainer()->get('doctrine')->getRepository('App:Task');
        $isDoneBefore = $taskRepository->findOneBy(['id' => '1'])->isDone();

        $this->client->request('POST', '/tasks/1/toggle', [
            '_token' => $this->getToken('POST', 'toggle')
        ]);
        $this->client->followRedirect();

        $isDoneAfter = $taskRepository->findOneBy(['id' => '1'])->isDone();

        $this->assertSame($isDoneBefore, !$isDoneAfter, "Problem to change isDone");
        $this->assertSelectorExists('.alert.alert-success', "no success flash message");
    }

    public function testTaskToggleChangeIsDoneGET()
    {
        $this->createLogin();

        $taskRepository = $this->getContainer()->get('doctrine')->getRepository('App:Task');
        $isDoneBefore = $taskRepository->findOneBy(['id' => '1'])->isDone();

        $this->client->request('GET', '/tasks/1/toggle?_token=' . $this->getToken('GET'));
        $this->client->followRedirect();

        $isDoneAfter = $taskRepository->findOneBy(['id' => '1'])->isDone();

        $this->assertSame($isDoneBefore, !$isDoneAfter, "Problem to change isDone");
        $this->assertSelectorExists('.alert.alert-success', "no success flash message");
    }

    public function testTaskToggleChangeIsDoneWithWrongTokenGET()
    {
        $this->createLogin();

        $taskRepository = $this->getContainer()->get('doctrine')->getRepository('App:Task');
        $isDoneBefore = $taskRepository->findOneBy(['id' => '1'])->isDone();

        $this->client->request('GET', '/tasks/1/toggle?_token=wrong_token');

        $isDoneAfter = $taskRepository->findOneBy(['id' => '1'])->isDone();

        $this->assertSame($isDoneBefore, $isDoneAfter, "change isDone with wrong token");
        $this->assertResponseRedirects('/logout');
    }

    public function testTaskToggleChangeIsDoneWithWrongTokenPOST()
    {
        $this->createLogin();

        $taskRepository = $this->getContainer()->get('doctrine')->getRepository('App:Task');
        $isDoneBefore = $taskRepository->findOneBy(['id' => '1'])->isDone();

        $this->client->request('POST', '/tasks/1/toggle', [
            '_token' => 'wrong_token'
        ]);

        $isDoneAfter = $taskRepository->findOneBy(['id' => '1'])->isDone();

        $this->assertSame($isDoneBefore, $isDoneAfter, "change isDone with wrong token");
        $this->assertResponseRedirects('/logout');
    }

    public function testTaskDeleteInaccessibleToAnonymous()
    {
        $this->client->request('GET', '/tasks/1/delete');

        $this->assertResponseRedirects('/tasks');
    }

    public function testTaskUserDeleteCorrectlyInDBForUser()
    {
        $this->createLogin();

        $taskRepository = $this->getContainer()->get('doctrine')->getRepository('App:Task');
        $id =  $this->getDeletedIds()[0];

        $this->client->request('POST', '/tasks/' . $id . '/delete', [
            "_token" => $this->getToken('POST', 'delete', $id)
        ]);

        $this->assertSame(null, $taskRepository->findOneBy(['id' => $id]), "Task not delete");
        $this->assertResponseRedirects('/tasks');
    }

    public function testTaskAdminDeleteCorrectlyInDBForAdmin()
    {
        $this->createLogin('Admin');

        $taskRepository = $this->getContainer()->get('doctrine')->getRepository('App:Task');

        $this->client->request('POST', '/tasks/1/delete', [
            "_token" => $this->getToken('POST', 'delete', 1)
        ]);

        $this->assertSame(null, $taskRepository->findOneBy(['id' => 1]), "Task not delete");
        $this->assertResponseRedirects('/tasks');
    }

    public function testTaskAnonymousDeleteCorrectlyInDBForAdmin()
    {
        $this->createLogin('Admin');

        $taskRepository = $this->getContainer()->get('doctrine')->getRepository('App:Task');

        $this->client->request('POST', '/tasks/3/delete', [
            "_token" => $this->getToken('POST', 'delete', 3)
        ]);

        $this->assertSame(null, $taskRepository->findOneBy(['id' => 3]), "Task not delete");
        $this->assertResponseRedirects('/tasks');
    }

    public function testTaskDeleteNotAccessibleForTaskNotBelongToUser()
    {
        $this->createLogin();

        $taskRepository = $this->getContainer()->get('doctrine')->getRepository('App:Task');
        $id =  $this->getUndeletedIds()[0];

        $this->client->request('POST', '/tasks/' . $id . '/delete', [
            "_token" => 'token'
        ]);

        $taskAfter = $taskRepository->findOneBy(['id' => $id]);

        $this->assertSame(true, (bool)$taskAfter, "Task should not be delete");
        $this->assertResponseRedirects('/tasks');
    }

    public function testTaskDeleteNotAccessibleForTaskNotBelongToAdmin()
    {
        $this->createLogin('Admin');

        $taskRepository = $this->getContainer()->get('doctrine')->getRepository('App:Task');
        $id = $this->getUndeletedIds()[0];

        $this->client->request('POST', '/tasks/' . $id . '/delete', [
            "_token" => 'token'
        ]);

        $taskAfter = $taskRepository->findOneBy(['id' => $id]);

        $this->assertSame(true, (bool)$taskAfter, "Task should not be delete");
        $this->assertResponseRedirects('/tasks');
    }

    public function testTaskUserDeleteWithWrongToken()
    {
        $this->createLogin();

        $taskRepository = $this->getContainer()->get('doctrine')->getRepository('App:Task');
        $id =  $this->getDeletedIds()[0];

        $this->client->request('POST', '/tasks/' . $id . '/delete', [
            "_token" => 'wrong_token'
        ]);

        $taskAfter = $taskRepository->findOneBy(['id' => $id]);

        $this->assertSame(true, (bool)$taskAfter, "Task should not be delete");
        $this->assertResponseRedirects('/logout');
    }


    private function getUndeletedIds(): array
    {
        $deletedId = $this->getDeletedIds();
        $undeleteId = [];
        for ($i = 1; $i <= 10; $i++) {
            if (!in_array($i, $deletedId)) {
                $undeleteId[] = $i;
            }
        }

        return $undeleteId;
    }

    private function getDeletedIds(): array
    {
        $crawler = $this->client->request('GET', '/tasks');
        $extract = $crawler->filterXPath('//form[contains(@action, "delete")]')
            ->evaluate('substring-after(@action, "/tasks/")');

        $deletedIds = [];
        foreach ($extract as $i => $id) {
            $deletedIds[] = substr($id, 0, 1);
        }

        return $deletedIds;
    }

    private function getToken($method, $action = null, $id = null)
    {
        $crawler = $this->client->request('GET', '/tasks');

        if ($method === 'POST' && $action === 'delete') {
            $extract = $crawler
                ->filter('form[action="/tasks/' . $id . '/' . $action . '"]>input[name="_token"]')
                ->extract(['value']);

            return $extract[0];
        }
        if ($method === 'POST' && $action === 'toggle') {
            $extract = $crawler
                ->filter('form[action="/tasks/1/' . $action . '"]>input[name="_token"]')->extract(['value']);

            return $extract[0];
        }

        if ($method === 'GET') {
            $extract = $crawler->filter('h4.isDoneLink>a')->extract(['href']);
            $url = $extract[0];

            return str_replace('_token=', '', strstr($url, '_token='));
        }
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
