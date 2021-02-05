<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class DefaultController extends AbstractController
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction()
    {
        if (!$this->isGranted('USER_CONNECT')) {
            return $this->redirectToRoute('app_login');
        }

        return $this->render('default/index.html.twig');
    }
}
