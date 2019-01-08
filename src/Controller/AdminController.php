<?php

namespace App\Controller;

use App\Entity\Squadron;
use App\Entity\User;
use App\Form\SquadronType;
use App\Repository\SquadronRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class AdminController extends AbstractController
{
    /**
     * @Route("/admin/squadron_settings", name="admin_squadron_settings")
     * @IsGranted("ROLE_ADMIN")
     */
    public function squadron_settings(Request $request, EntityManagerInterface $em, SquadronRepository $squadronRepository)
    {

        $user = $this->getUser();

        /**
         * @var Squadron data
         */
        $data = $squadronRepository->findOneBy(['id' => $user->getSquadron()->getId()]);
//        dd($squad);
        $form = $this->createForm(SquadronType::class, $data);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {

            /**
             * @var Squadron $squad
             */
            $squad = $form->getData();
            $em->flush();

            $this->addFlash('success','Your settings have been saved.');
            $data = $squad;
        }

        return $this->render('admin/squadron_settings.html.twig', [
            'form_template' => $form->createView(),
            'title' => 'Squadron Settings',
            'squad' => $data
        ]);
    }
}
