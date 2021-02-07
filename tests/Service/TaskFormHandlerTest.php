<?php

namespace App\Tests\Service;

use App\Entity\Task;
use App\Service\TaskFormHandler;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Request;

class TaskFormHandlerTest extends KernelTestCase
{
    use FixturesTrait;

    private $form;
    private $em;
    private $session;
    private $sessionBag;
    private $request;

    public function setUp(): void
    {
        $this->form = $this->getMockBuilder('Symfony\Component\Form\Form')
            ->disableOriginalConstructor()
            ->setMethods(['handleRequest', 'isSubmitted', 'isValid'])
            ->getMock();

        $this->em = $this->getMockBuilder('Doctrine\ORM\EntityManagerInterface')
            ->getMock();

        $this->session = $this->getMockBuilder('Symfony\Component\HttpFoundation\Session\SessionInterface')
            ->setMethods(['start', 'getId', 'setId', 'getName', 'setName', 'invalidate', 'migrate', 'save', 'has', 'get', 'set', 'all', 'replace', 'remove', 'clear', 'isStarted', 'registerBag', 'getBag', 'getMetadataBag'])
            ->getMock();

        $this->sessionBag = $this->getMockBuilder('Symfony\Component\HttpFoundation\Session')
            ->setMethods(['add'])
            ->getMock();

        $this->request = new Request();
    }

    public function testHandleFormReturnFalseWhenFormIsNotSubmitted()
    {
        $result = $this->handle(new Task(), false, false);

        $this->assertSame(false, $result);
    }

    public function testHandleFormReturnFalseWhenFormIsSubmittedAndNotValid()
    {
        $result = $this->handle(new Task(), true, false);

        $this->assertSame(false, $result);
    }

    public function testHandleFormReturnTrueWhenCreateFormIsSubmittedAndValid()
    {
        $result = $this->handle(new Task(), true, true);

        $this->assertSame(true, $result);
    }

    public function testHandleFormReturnTrueWhenEditFormIsSubmittedAndValid()
    {
        $task = $this->getMockBuilder('App\Entity\Task')->setMethods(['getId'])->getMock();
        $task->method('getId')->willReturn(1);

        $result = $this->handle($task, true, true);

        $this->assertSame(true, $result);
    }


    private function handle(Task $task, ?bool $submitted = null, ?bool $valid = null): bool
    {
        $this->setFormMethods($task, $submitted, $valid);

        $handleForm = new TaskFormHandler($this->em, $this->session);
        return $handleForm->handle($this->request, $this->form, $task);
    }

    private function setFormMethods(Task $task, ?bool $submitted = null, ?bool $valid = null)
    {
        $submittedTrue = $submitted ? 'once' : 'never';
        $submittedValidTrue = $submitted && $valid ? 'once' : 'never';
        $submittedValidCreateForm = $submitted && $valid && !$task->getId() ? 'once' : 'never';
        $flashMessage = $submitted && $valid && !$task->getId() ? 'ajoutée' : 'modifiée';

        $this->em->expects($this->$submittedValidCreateForm())->method('persist');
        $this->em->expects($this->$submittedValidTrue())->method('flush');

        $this->session->expects($this->$submittedValidTrue())->method('getBag')->willReturn($this->sessionBag);
        $this->sessionBag
            ->expects($this->$submittedValidTrue())
            ->method('add')
            ->with($this->equalTo('success'), $this->stringContains($flashMessage));

        $this->form
            ->expects($this->once())
            ->method('handleRequest')
            ->willReturn($this->form);
        $this->form
            ->expects($this->once())
            ->method('isSubmitted')
            ->willReturn($submitted);
        $this->form
            ->expects($this->$submittedTrue())
            ->method('isValid')
            ->willReturn($valid);
    }
}
