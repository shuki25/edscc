<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class AjaxController extends AbstractController
{
    /**
     * @Route("/ajax/{verb}", name="ajax")
     */
    public function ajax($verb)
    {
        return $this->render('ajax/index.html.twig', [
            'controller_name' => 'AjaxController',
            'title' => $verb
        ]);
    }
}
