<?php

namespace App\Controller;

use App\Entity\Motd;
use App\Entity\User;
use App\Form\MotdType;
use App\Repository\MotdRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class MotdController extends AbstractController
{

    /**
     * @var TranslatorInterface
     */
    private $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * @Route("/admin/motd", name="admin_list_motd")
     * @IsGranted("ROLE_SUPERUSER")
     */
    public function listMotd()
    {
        return $this->render('admin/list_motd_datatables.html.twig', [
            'title' => 'Members List'
        ]);
    }

    /**
     * @Route("/admin/motd/new/{token}", name="admin_motd_new", methods={"GET","POST"})
     * @IsGranted("ROLE_SUPERUSER")
     */
    public function newMotd($token, Request $request)
    {
        /**
         * @var Motd $data
         */
        $data = new Motd();
        $em = $this->getDoctrine()->getManager();

        $form = $this->createForm(MotdType::class, $data);
        $form->handleRequest($request);

        if (!$this->isCsrfTokenValid('new_motd', $token)) {
            $this->addFlash('success', $this->translator->trans('Expired CSRF Token. Please refresh the page to continue.'));
            return $this->redirectToRoute('admin_list_motd');
        }

        if ($form->isSubmitted() && $form->isValid()) {
            /**
             * @var Motd $motd
             */
            $motd = $form->getData();
            $em->persist($motd);
            $em->flush();

            $this->addFlash('success', $this->translator->trans('New MOTD has been added.'));
            $data = $motd;

            return $this->redirectToRoute('admin_list_motd');
        }
        return $this->render('admin/edit_motd.html.twig', [
            'form_template' => $form->createView(),
            'title' => $this->translator->trans('Manage MOTD')
        ]);
    }

    /**
     * @Route("/admin/motd/{slug}/edit/{token}", name="admin_motd_edit", methods={"GET","POST"})
     * @IsGranted("ROLE_SUPERUSER")
     */
    public function editMotd($slug, $token, Request $request, MotdRepository $repository)
    {
        $em = $this->getDoctrine()->getManager();
        $data = $repository->findOneBy(['id' => $slug]);

        if (!is_object($data)) {
            $this->addFlash('alert', $this->translator->trans('Permission Denied. Unable to access to this resource.'));
            return $this->redirectToRoute('admin_list_motd');
        }
        $form = $this->createForm(MotdType::class, $data);
        $form->handleRequest($request);

        if (!$this->isCsrfTokenValid('edit_motd', $token)) {
            $this->addFlash('success', $this->translator->trans('Expired CSRF Token. Please refresh the page to continue.'));
            return $this->redirectToRoute('admin_list_motd');
        }

        if ($request->request->get('cancel')) {
            $this->addFlash('alert', $this->translator->trans('Changes were not saved.'));
            return $this->redirectToRoute('admin_list_motd');
        }

        if ($form->isSubmitted() && $form->isValid()) {
            /**
             * @var Motd $motd
             */
            $motd = $form->getData();
            $em->flush();

            $this->addFlash('success', $this->translator->trans('Your changes have been updated.'));
            $data = $motd;

            return $this->redirectToRoute('admin_list_motd');
        }

        return $this->render('admin/edit_motd.html.twig', [
            'form_template' => $form->createView(),
            'title' => $this->translator->trans('Manage MOTD'),
            'isEdit' => true
        ]);
    }
}
