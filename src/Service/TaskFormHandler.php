<?php


namespace App\Service;

use App\Entity\Task;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;

class TaskFormHandler
{
    private $em;
    private $flash;
    private $flashMessage = 'La tâche a été modifiée avec succès.';

    public function __construct(EntityManagerInterface $em, FlashBagInterface $flash)
    {
        $this->em = $em;
        $this->flash = $flash;
    }

    public function handle(Request $request, Form $form, Task $task = null): bool
    {
        $form->handleRequest($request);

        if (!$form->isSubmitted() || !$form->isValid()) {
            return false;
        }

        if (!$task->getId()) {
            $this->em->persist($task);
            $this->flashMessage = 'La tâche a été ajoutée avec succès.';
        }

        $this->flash->add('success', $this->flashMessage);

        $this->em->flush();

        return true;
    }
}
