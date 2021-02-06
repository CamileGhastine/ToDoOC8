<?php

namespace App\DataFixtures;

use App\Entity\Task;
use App\Entity\User;
use DateTime;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class TaskFixtures extends Fixture implements dependentFixtureInterface
{
    private const NAMES = ['Admin', 'Camile', null];

    public function load(ObjectManager $manager)
    {
        for ($i = 1; $i <= 3; $i++) {
            $task = new Task();

            $name = self::NAMES[$i - 1];

            $task->setTitle('tâche n°' . $i);
            $task->setContent('la tâche n°' . $i . ' est très importante');
            $task->setCreatedAt(new DateTime());
            $task->toggle(rand(0, 1));
            if ($name) {
                $task->setUser($this->getReference($name));
            }

            $manager->persist($task);
        }

        for ($i = 4; $i <= 10; $i++) {
            $task = new Task();

            /** @var User $user */
            $user = $this->getUser();

            $task->setTitle('tâche n°' . $i);
            $task->setContent('la tâche n°' . $i . ' est très importante');
            $task->setCreatedAt(new DateTime());
            $task->toggle(rand(0, 1));
            if ($user) {
                $task->setUser($user);
            }

            $manager->persist($task);
        }

        $manager->flush();
    }

    /**
     * @return bool|object
     */
    public function getUser()
    {
        $name = self::NAMES[rand(0, 2)];

        if (!$name) {
            return false;
        }

        return $this->getReference($name);
    }

    /**
     * @return string[]
     */
    public function getDependencies()
    {
        return [
            UserFixtures::class,
        ];
    }
}
