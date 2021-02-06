<?php

namespace App\Tests\Entity;

use App\DataFixtures\UserFixtures;
use App\Entity\Task;
use App\Entity\User;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Validator\ConstraintViolation;

class UserTest extends KernelTestCase
{
    use FixturesTrait;

    private function getUser(): User
    {
        return (new User())
            ->setUsername('username')
            ->setEmail('email@domaine.fr')
            ->setPassword('Password1')
            ;
    }

    private function assertHasErrors(int $number, $user)
    {
        self::bootKernel();
        $errors = self::$container->get('validator')->validate($user);

        $messages = [];
        /** @var ConstraintViolation $error */
        foreach ($errors as $error) {
            $messages[] = $error->getPropertyPath() . ' => ' . $error->getMessage();
        }

        static::assertCount($number, $errors, implode(' - ', $messages));
    }


    public function testValidUser()
    {
        $this->assertHasErrors(0, $this->getUser());
    }

    public function testNotBlankUsername()
    {
        $user = ($this->getUser())
            ->setUsername('');
        $this->assertHasErrors(2, $user);
    }

    public function testShortUsername()
    {
        $user = ($this->getUser())
            ->setUsername('a');
        $this->assertHasErrors(1, $user);

        // Username just long enough (2 characters)
        $user = ($this->getUser())
            ->setUsername('ab');
        $this->assertHasErrors(0, $user);
    }

    public function testLongUsername()
    {
        $username25 = '';
        for ($i = 0; $i < 25; $i++) {
            $username25 .= 'a';
        }

        $user = ($this->getUser())
            ->setUsername($username25 . 'a');
        $this->assertHasErrors(1, $user);

        // Username reach limit of 25 characters
        $user = ($this->getUser())
            ->setUsername($username25);
        $this->assertHasErrors(0, $user);
    }

    public function testRegexUsername()
    {
        $user = ($this->getUser())
            ->setUsername(' afff');
        $this->assertHasErrors(1, $user);

        $user = ($this->getUser())
            ->setUsername('?afff');
        $this->assertHasErrors(1, $user);
    }

    public function testUniqueUsername()
    {
        $user = ($this->getUser())
            ->setUsername('Admin');

        $this->loadFixtures([UserFixtures::class]);
        $this->assertHasErrors(1, $user);
    }

    public function testValidPaswword()
    {
        $user = ($this->getUser())
            ->setPassword('Abcde1');
        $this->assertHasErrors(0, $user);
    }

    public function testNotBlankPassword()
    {
        $user = ($this->getUser())
            ->setPassword('');
        $this->assertHasErrors(2, $user);
    }


    public function testShortPassword()
    {
        $user = ($this->getUser())
            ->setPassword('Abcd1');
        $this->assertHasErrors(1, $user);
    }

    public function testLongPassword()
    {
        $password25 = 'A1';
        for ($i = 0; $i < 98; $i++) {
            $password25 .= 'a';
        }
        $user = ($this->getUser())
            ->setPassword($password25 . 'a');
        $this->assertHasErrors(1, $user);

        // password reach limit of 100 characters
        $user = ($this->getUser())
            ->setPassword($password25);
        $this->assertHasErrors(0, $user);
    }

    public function testRegexPassword()
    {
        $user = ($this->getUser())
            ->setPassword('abcde1');
        $this->assertHasErrors(1, $user);

        $user = ($this->getUser())
            ->setPassword('Abcdef');
        $this->assertHasErrors(1, $user);

        $user = ($this->getUser())
            ->setPassword('ABCDE1');
        $this->assertHasErrors(1, $user);
    }

    public function testValidEmail()
    {
        $user = ($this->getUser())
            ->setEmail('username@domain.com');
        $this->assertHasErrors(0, $user);
    }

    public function testNotBlankEmail()
    {
        $user = ($this->getUser())
            ->setEmail('');
        $this->assertHasErrors(1, $user);
    }

    public function testFormatEmail()
    {
        $user = ($this->getUser())
            ->setEmail('username@domain');
        $this->assertHasErrors(1, $user);

        $user = ($this->getUser())
            ->setEmail('@domain.com');
        $this->assertHasErrors(1, $user);

        $user = ($this->getUser())
            ->setEmail('username@.com');
        $this->assertHasErrors(1, $user);
    }

    public function testUniqueEmail()
    {
        $user = ($this->getUser())
            ->setEmail('Admin@todoco.fr');

        $this->loadFixtures([UserFixtures::class]);
        $this->assertHasErrors(1, $user);
    }

    public function testSetGetRemoveTasks()
    {
        $user = $this->getUser();
        $task = (new Task())
            -> setTitle('new title')
            -> setContent('new content')
        ;

        $user->addTask($task);
        $this->assertSame($task, $user->getTasks()[0]);

        $user->removeTask($task);
        $this->assertSame(null, $user->getTasks()[0]);
    }

    public function testSetGetToken()
    {
        $user = $this->getUser();
        $token = 'a new token';
        $user->setToken($token);

        $this->assertSame($token, $user->getToken());
    }
}
