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

             $task -> setTitle('tache n°$i');
             $task -> setContent('la tache n°$i est très importante');
             $task -> setCreatedAt(new DateTime());
             if($i < rand(1,8)) {
                 $task -> setUser($this->getReference('camile'));
             }

             $manager->persist($task);
        }

        $manager->flush();
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
