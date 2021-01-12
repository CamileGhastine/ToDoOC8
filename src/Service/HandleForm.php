<?php


namespace App\Service;

use App\Entity\Task;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;

class HandleForm
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function handle(Request $request, Form $form, Task $task = null)
    {
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($task) {
                $this->em->persist($task);
            }
            $this->em->flush();

            return false;
        }

        return $form;
    }

}