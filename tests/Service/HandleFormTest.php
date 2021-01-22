<?php


namespace App\Tests\Service;


use App\Controller\TaskController;
use App\Entity\Task;
use App\Form\TaskType;
use App\Service\HandleForm;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\HttpFoundation\Request;

class HandleFormTest extends TestCase
{
    private $form;
    private $em;
    private $request;



    public function testA() {
        $task = new Task();
        $formData = [
            'tit'
        ];
        $form = $this->factory->create(TaskType::class, $task);
        $form->submit()

        dd($form);
    }


    public function testHandleFormReturnFormWhenFormIsNotSubmitted() {
        $result = $this->handle(new Task(), false, false);

        $this->assertSame($this->form, $result);
    }

    public function testHandleFormReturnFalseWhenFormIsSubmittedAndValid() {
        $result = $this->handle(new Task(), true, true);

        $this->assertSame(false, $result);
    }

    public function testHandleFormReturnFalseWhenFormIsSubmittedAndValidAndTaskIsNull() {
        $result = $this->handle(null, true, true);

        $this->assertSame(false, $result);
    }

    private function handle(?Task $task = null, ?bool $submitted = null, ?bool $valid = null){
        $this->setFormMethods($submitted, $valid);

        $handleForm = new HandleForm($this->em);
        return $handleForm->handle($this->request, $this->form, $task);
    }

    private function setFormMethods(?bool $submitted = null, ?bool $valid = null) {
        $expects = $valid ? 'once' : 'never';
        $test = 'once';

        $this->form
            ->expects($this->$test())
            ->method('handleRequest')
            ->willReturn($this->form);
        $this->form
            ->expects($this->$test())
            ->method('isSubmitted')
            ->willReturn($submitted);
        $this->form
            ->expects($this->$expects())
            ->method('isValid')
            ->willReturn($valid);
    }

}