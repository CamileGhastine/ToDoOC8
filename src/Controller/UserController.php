<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Service\UserFormHandler;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class UserController
 * @package App\Controller
 */
class UserController extends AbstractController
{
    /**
     * @Route("/users", name="user_list")
     */
    public function listAction()
    {
        $this->denyAccessUnlessGranted('ADMIN');

        return $this->render('user/list.html.twig', ['users' => $this->getDoctrine()->getRepository('App:User')->findAll()]);
    }

    /**
     * @Route("/users/create", name="user_create")
     * @param Request $request
     * @param UserFormHandler $userFormHandler
     * @return RedirectResponse|Response
     */
    public function createAction(Request $request, UserFormHandler $userFormHandler)
    {
        $user = new User();

        /** @var Form $form */
        $form = $this->createForm(UserType::class, $user);

        if ($userFormHandler->handle($request, $form, $user)) {
            return $this->redirectToRoute('login');
        }

        return $this->render('user/create.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @Route("/users/{id}/edit", name="user_edit")
     * @param User $user
     * @param Request $request
     * @param UserFormHandler $userFormHandler
     * @return RedirectResponse|Response
     */
    public function editAction(User $user, Request $request, UserFormHandler $userFormHandler)
    {
        $this->denyAccessUnlessGranted('ADMIN');

        /** @var Form $form */
        $form = $this->createForm(UserType::class, $user);

        if ($userFormHandler->handle($request, $form, $user)) {
            return $this->redirectToRoute('user_list');
        }

        return $this->render('user/edit.html.twig', ['form' => $form->createView(), 'user' => $user]);
    }
}
