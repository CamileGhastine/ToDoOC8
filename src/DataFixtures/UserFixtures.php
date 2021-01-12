<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;

class UserFixtures extends Fixture
{
    private $passwordEncoder;
    private $token;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder, TokenGeneratorInterface $token)
    {
        $this->passwordEncoder = $passwordEncoder;
        $this->token = $token;
    }

    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $user = new User();
        $user -> setUsername('camile');
        $user -> setEmail('camile@todo.fr');
        $user -> setPassword($this->passwordEncoder->encodePassword($user, 'camile'));

        $this->addReference('camile', $user);

        $manager->persist($user);

        $manager->flush();
    }
}
