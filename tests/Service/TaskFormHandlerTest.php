<?php


namespace App\Tests\Service;

use App\Entity\Task;
use App\Service\TaskFormHandler;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

class TaskFormHandlerTest extends TestCase
{
    private $form;
    private $em;
    private $request;

    public function setUp()
    {
        $this->form = $this->getMockBuilder('Symfony\Component\Form\Form')
            ->disableOriginalConstructor()
            ->setMethods(['handleRequest', 'isSubmitted', 'isValid'])
            ->getMock();

        $this->em = $this->getMockBuilder('Doctrine\ORM\EntityManagerInterface')
            ->getMock();

        $this->request = new Request();
    }

    public function testHandleFormReturnFormWhenFormIsNotSubmitted()
    {
        $result = $this->handle(new Task(), false, false);

        $this->assertSame($this->form, $result);
    }

    public function testHandleFormReturnFalseWhenFormIsSubmittedAndNotValid()
    {
        $result = $this->handle(new Task(), true, false);

        $this->assertSame($this->form, $result);
    }

    public function testHandleFormReturnFalseWhenFormIsSubmittedAndValid()
    {
        $result = $this->handle(new Task(), true, true);

        $this->assertSame(false, $result);
    }

    public function testHandleFormReturnFalseWhenFormIsSubmittedAndValidAndTaskIsNull()
    {
        $result = $this->handle(null, true, true);

        $this->assertSame(false, $result);
    }

    private function handle(?Task $task = null, ?bool $submitted = null, ?bool $valid = null)
    {
        $this->setFormMethods($task, $submitted, $valid);

        $handleForm = new TaskFormHandler($this->em);
        return $handleForm->handle($this->request, $this->form, $task);
    }

    private function setFormMethods(?Task $task, ?bool $submitted = null, ?bool $valid = null)
    {
        $submittedTrue = $submitted ? 'once' : 'never';
        $SubmitedValidTrue = $submitted && $valid ? 'once' : 'never';
        $SubmitedValidTaskTrue = $submitted && $valid && $task ? 'once' : 'never';

        $this->em->expects($this->$SubmitedValidTaskTrue())->method('persist');
        $this->em->expects($this->$SubmitedValidTrue())->method('flush');

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
