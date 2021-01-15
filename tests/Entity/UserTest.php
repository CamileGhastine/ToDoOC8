<?php


namespace App\Tests\Entity;


use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Validator\ConstraintViolation;

class UserTest extends KernelTestCase
{

    public function getUser() {
        return (new User())
            ->setUsername('username')
            ->setEmail('email@domaine.fr')
            ->setPassword('password')
            ;
    }

    public function assertHasErrors(int $number, $user) {
        self::bootKernel();
        $errors = self::$container->get('validator')->validate($user);

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
    public function testValidUser(){
        $this->assertHasErrors(0, $this->getUser());
    }

}