<?php

namespace App\Controller;

use App\Entity\Task;
use App\Form\TaskType;
use App\Service\TaskFormHandler;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

class TaskController extends AbstractController
{
    /**
     * @Route("/tasks", name="task_list")
     */
    public function listAction()
    {
        return $this->render('task/list.html.twig', ['tasks' => $this->getDoctrine()->getRepository('App:Task')->findAll()]);
    }

    /**
     * @Route("/tasks/create", name="task_create")
     *
     * @param Form $form
     */
    public function createAction(Request $request, TaskFormHandler $handleForm)
    {
        $task = new Task($this->getUser());
        /** @var Form $form */
        $form = $this->createForm(TaskType::class, $task);

        $form = $handleForm->handle($request, $form, $task);
        if (!$form) {
            $this->addFlash('success', 'La tâche a bien été  ajoutée.');

            return $this->redirectToRoute('task_list');
        }

        return $this->render('task/create.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @Route("/tasks/{id?null}/edit", name="task_edit")
     *
     * @param Request $request
     * @param Task $task
     * @param TaskFormHandler $handleForm
     *
     * @return RedirectResponse|Response
     */
    public function editAction(Request $request, Task $task, TaskFormHandler $handleForm)
    {
        /** @var Form $form */
        $form = $this->createForm(TaskType::class, $task);

        $form = $handleForm->handle($request, $form, $task);
        if (!$form) {
            $this->addFlash('success', 'La tâche a été modifiée avec succès.');

            return $this->redirectToRoute('task_list');
        }

        return $this->render('task/edit.html.twig', [
            'form' => $form->createView(),
            'task' => $task,
        ]);
    }

    /**
     * @Route("/tasks/{id}/toggle", name="task_toggle")
     */
    public function toggleTaskAction(Task $task)
    {
        $task->toggle(!$task->isDone());
        $this->getDoctrine()->getManager()->flush();

        $this->addFlash('success', sprintf('La tâche %s a été marquée comme faite.', $task->getTitle()));

        return $this->redirectToRoute('task_list');
    }

    /**
     * @Route("/tasks/{id}/delete", name="task_delete")
     */
    public function deleteTaskAction(Task $task)
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($task);
        $em->flush();

        $this->addFlash('success', 'La tâche a été supprimée avec succès.');

        return $this->redirectToRoute('task_list');
    }
}
