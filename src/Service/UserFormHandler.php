<?php

namespace App\Service;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserFormHandler
{
    private $em;
    private $passwordEncoder;
    private $flash;
    private $flashMessage =  "L'utilisateur a bien été modifié avec succès.";

    public function __construct(
        EntityManagerInterface $em,
        UserPasswordEncoderInterface $passwordEncoder,
        FlashBagInterface $flash
    ) {
        $this->em = $em;
        $this->passwordEncoder = $passwordEncoder;
        $this->flash = $flash;
    }

    public function handle(Request $request, Form $form, User $user): bool
    {
        $form->handleRequest($request);

        if (!$form->isSubmitted() || !$form->isValid()) {
            return false;
        }

        if (!$user->getId()) {
            $password = $this->passwordEncoder->encodePassword($user, $user->getPassword());
            $user->setPassword($password);

            $this->em->persist($user);
            $this->flashMessage = "L'utilisateur a bien été ajouté avec succès.";
        }

        $this->flash->add('success', $this->flashMessage);

        $this->em->flush();

        return true;
    }
}
