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
    public function load(ObjectManager $manager)
    {
        for ($i=0; $i<10; $i++) {
            $task = new Task();

            /** @var User $user */
            $user = $this->getUser();

            $task -> setTitle('tache n°'.$i);
            $task -> setContent('la tache n°'.$i.' est très importante');
            $task -> setCreatedAt(new DateTime());
            if ($user) {
                $task -> setUser($user);
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
        $arrayName = ['Admin', 'Camile', null];
        $name = $arrayName[rand(0, 2)];

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
