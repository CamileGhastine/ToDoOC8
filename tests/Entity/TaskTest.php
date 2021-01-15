<?php

namespace App\Tests\Entity;


use App\Entity\Task;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Validator\ConstraintViolation;

class TaskTest extends KernelTestCase
{
    public function getTask() : Task {
        return (new Task())
            ->setTitle('Title')
            ->setContent('Content')
            ;
    }

    public function assertHasErrors(int $number, Task $task) {
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

}