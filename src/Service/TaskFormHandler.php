<?php

namespace App\Service;

use App\Entity\Task;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class TaskFormHandler
{
    private $em;
    private $session;
    private $flashMessage = 'La tâche a été modifiée avec succès.';

    public function __construct(EntityManagerInterface $em, SessionInterface $session)
    {
        $this->em = $em;
        $this->session = $session;
    }

    public function handle(Request $request, Form $form, Task $task): bool
    {
        $form->handleRequest($request);

        if (!$form->isSubmitted() || !$form->isValid()) {
            return false;
        }

        if (!$task->getId()) {
            $this->em->persist($task);
            $this->flashMessage = 'La tâche a été ajoutée avec succès.';
        }

        $this->session->getBag('flashes')->add('success', $this->flashMessage);

        $this->em->flush();

        return true;
    }
}
