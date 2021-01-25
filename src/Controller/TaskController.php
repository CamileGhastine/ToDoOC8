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
    public function listAction(): Response
    {
        return $this->render('task/list.html.twig', ['tasks' => $this->getDoctrine()->getRepository('App:Task')->findAll()]);
    }

    /**
     * @Route("/tasks/create", name="task_create")
     *
     * @param Request $request
     * @param TaskFormHandler $handleForm
     * @return RedirectResponse|Response
     */
    public function createAction(Request $request, TaskFormHandler $handleForm)
    {
        $task = new Task($this->getUser());
        /** @var Form $form */
        $form = $this->createForm(TaskType::class, $task);

        if ($handleForm->handle($request, $form, $task)) {

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

        if ($handleForm->handle($request, $form, $task)) {
            return $this->redirectToRoute('task_list');
        }

        return $this->render('task/edit.html.twig', [
            'form' => $form->createView(),
            'task' => $task,
        ]);
    }

    /**
     * @Route("/tasks/{id}/toggle", name="task_toggle")
     * @param Task $task
     * @return RedirectResponse
     */
    public function toggleTaskAction(Task $task): RedirectResponse
    {
        $task->toggle(!$task->isDone());
        $this->getDoctrine()->getManager()->flush();

        $this->addFlash('success', sprintf('La tâche %s a été marquée comme faite.', $task->getTitle()));

        return $this->redirectToRoute('task_list');
    }

    /**
     * @Route("/tasks/{id}/delete", name="task_delete")
     * @param Task $task
     * @return RedirectResponse
     */
    public function deleteTaskAction(Task $task): RedirectResponse
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($task);
        $em->flush();

        $this->addFlash('success', 'La tâche a été supprimée avec succès.');

        return $this->redirectToRoute('task_list');
    }
}
