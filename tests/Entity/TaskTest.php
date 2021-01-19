<?php

namespace App\Tests\Entity;


use App\Entity\Task;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Validator\ConstraintViolation;

class TaskTest extends KernelTestCase
{
    private function getTask() : Task {
        return (new Task())
            ->setTitle('Title')
            ->setContent('Content')
            ;
    }

    private function assertHasErrors(int $number, Task $task) {
        self::bootKernel();
        $errors = self::$container->get('validator')->validate($task);

        $messages=[];
        /** @var ConstraintViolation $error */
        foreach ($errors as $error) {
            $messages[] = $error->getPropertyPath().' => '. $error->getMessage();
        }

        $this->assertCount($number, $errors, implode(' - ', $messages));
    }


    /**
     * Test valid entity
     */
    public function testValidTask(){
        $this->assertHasErrors(0, $this->getTask());
    }

    /**
     * Test notBlank constraint on title
     */
    public function testNotBlankTitle(){

        $task = ($this->getTask())
            ->setTitle('');
        $this->assertHasErrors(2, $task);
    }


    /**
     * Test short title
     */
    public function testShortTitle(){

        $task = ($this->getTask())
            ->setTitle('a');
        $this->assertHasErrors(1, $task);

        // Title just long enough (2 characters)
        $task = ($this->getTask())
            ->setTitle('ab');
        $this->assertHasErrors(0, $task);

    }

    /**
     * Test to long title constraint
     */
    public function testLongTitle(){

        $title50 = '';
        for ($i=0; $i<50; $i++) {
            $title50.='a';
        }

        $task = ($this->getTask())
            ->setTitle($title50.'a');
        $this->assertHasErrors(1, $task);

        // Title reach limit of 50 characters
        $task = ($this->getTask())
            ->setTitle($title50);
        $this->assertHasErrors(0, $task);
    }

    /**
     * Test notBlank constraint on  content
     */
    public function testNotBlankContent(){

        $task = ($this->getTask())
            ->setContent('');
        $this->assertHasErrors(1, $task);
    }

    /**
     * Test isDone set as false when create new Task
     */
    public function testIsDoneFalse() {
        $task = $this->getTask();
        $this->assertSame(false, $task->isDone());
    }

    /**
     * Test instance of user
     */
    public function testInstanceOfUser() {
        $task = ($this->getTask())
            ->setUser(new User());
        $this->assertHasErrors(0, $task);
    }

    /**
     * Test null given for user
     */
    public function testNullUser() {
        $task = ($this->getTask())
            ->setUser(null);
        $this->assertHasErrors(0, $task);
    }
}