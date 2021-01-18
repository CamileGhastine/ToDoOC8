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
        $names = ['Admin', 'Camile'];

        foreach ($names as $name) {
            $user = new User();
            $user -> setUsername($name)
            -> setEmail($name.'@todoco.fr')
            -> setPassword($this->passwordEncoder->encodePassword($user, $name.'1'));

            if ($name === 'Admin') {
                $user -> setRole('ROLE_ADMIN');
            }

            $this->addReference($name, $user);

            $manager->persist($user);
        }


        $manager->flush();
    }
}
