<?php


namespace App\Tests\Entity;


use App\DataFixtures\UserFixtures;
use App\Entity\User;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Validator\ConstraintViolation;

class UserTest extends KernelTestCase
{
//    use FixturesTrait;

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

    /**
     * Test notBlank constraint on username
     */
    public function testNotBlankUsername(){

        $user = ($this->getUser())
            ->setUsername('');
        $this->assertHasErrors(2, $user);
    }


    /**
     * Test short username
     */
    public function testShortUsername(){

        $user = ($this->getUser())
            ->setUsername('a');
        $this->assertHasErrors(1, $user);

        // Username just long enough (2 characters)
        $user = ($this->getUser())
            ->setUsername('ab');
        $this->assertHasErrors(0, $user);

    }

    /**
     * Test to long username constraint
     */
    public function testLongUsername(){

        $username25 = '';
        for ($i=0; $i<25; $i++) {
            $username25.='a';
        }

        $user = ($this->getUser())
            ->setUsername($username25.'a');
        $this->assertHasErrors(1, $user);

        // Username reach limit of 50 characters
        $user = ($this->getUser())
            ->setUsername($username25);
        $this->assertHasErrors(0, $user);
    }

    /**
     * Test regex of username
     */
    public function testFormatUsername(){

        $user = ($this->getUser())
            ->setUsername(' afff');
        $this->assertHasErrors(1, $user);

        $user = ($this->getUser())
            ->setUsername('?afff');
        $this->assertHasErrors(1, $user);
    }
}