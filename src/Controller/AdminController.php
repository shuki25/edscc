<?php

namespace App\Controller;

use App\Entity\Announcement;
use App\Entity\Squadron;
use App\Entity\User;
use App\Form\AnnouncementType;
use App\Form\SquadronType;
use App\Repository\AnnouncementRepository;
use App\Repository\SquadronRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class AdminController extends AbstractController
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

            $this->addFlash('success',$this->translator->trans('Your settings have been saved.'));
            $data = $squad;
        }

        return $this->render('admin/squadron_settings.html.twig', [
            'form_template' => $form->createView(),
            'title' => 'Squadron Settings',
            'squad' => $data
        ]);
    }

    /**
     * @Route("/admin/announcements", name="admin_list_announcements")
     * @IsGranted("ROLE_ADMIN")
     */
    public function list_announcements(Request $request, EntityManagerInterface $em, AnnouncementRepository $repository, PaginatorInterface $paginator)
    {

        return $this->render('admin/list_announcements_datatables.html.twig', [
            'title' => 'Members List'
        ]);



//        /**
//         * @var User $user
//         */
//        $user = $this->getUser();
//
//        $q = $request->query->get('q') ?: '';
//        $query = $repository->findAllBySquadron($user->getSquadron()->getId(), $q);
//        $size = $request->query->get('size') ?: '10';
//
//        $pagination = $paginator->paginate($query, $request->query->getInt('page', 1), $size);
//
//        return $this->render('admin/list_announcements.html.twig', [
//            'title' => 'Manage Announcements',
//            'pagination' => $pagination,
//            'query' => $request->query->all(),
//            'size' => $size
//        ]);

    }

    /**
     * @Route("/admin/announcements/new/{token}", name="admin_announcements_new", methods={"GET","POST"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function new_announcements($token, Request $request, EntityManagerInterface $em, AnnouncementRepository $repository)
    {
        /**
         * @var User $user
         */
        $user = $this->getUser();

        /**
         * @var Announcement $data
         */
        $data = new Announcement();
        $data ->setUser($user)->setSquadron($user->getSquadron());

        $form = $this->createForm(AnnouncementType::class, $data);
        $form->handleRequest($request);

        if (!$this->isCsrfTokenValid('new_announcement', $token)) {
            $this->addFlash('success', $this->translator->trans('Invalid CSRF Token. Please refresh the page to continue.'));
            return $this->redirectToRoute('admin_list_announcements');
        }

        if ($form->isSubmitted() && $form->isValid()) {
            /**
             * @var Announcement $announcement
             */
            $announcement = $form->getData();
            $em->persist($announcement);
            $em->flush();

            $this->addFlash('success',$this->translator->trans('New announcement has been added.'));
            $data = $announcement;

            return $this->redirectToRoute('admin_list_announcements');
        }
        return $this->render('admin/edit_announcement.html.twig', [
            'form_template' => $form->createView(),
            'title' => $this->translator->trans('Manage Announcements')
        ]);
    }

    /**
     * @Route("/admin/announcements/{slug}/edit/{token}", name="admin_announcements_edit", methods={"GET","POST"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function edit_announcements($slug, $token, Request $request, EntityManagerInterface $em, AnnouncementRepository $repository)
    {
        /**
         * @var User $user
         */
        $user = $this->getUser();

        $data = $repository->findOneBy(['id'=>$slug, 'squadron' => $user->getSquadron()->getId()]);

        if(!is_object($data)) {
            $this->addFlash('alert',$this->translator->trans('Permission Denied. Unable to access to this resource.'));
            return $this->redirectToRoute('admin_list_announcements');
        }
        $form = $this->createForm(AnnouncementType::class, $data);
        $form->handleRequest($request);

        if(!$this->isCsrfTokenValid('edit_announcement',$token)) {
            $this->addFlash('success',$this->translator->trans('Invalid CSRF Token. Please refresh the page to continue.'));
            return $this->redirectToRoute('admin_list_announcements');
        }

        if($request->request->get('cancel')) {
            $this->addFlash('alert', $this->translator->trans('Changes were not saved.'));
            return $this->redirectToRoute('admin_list_announcements');
        }

        if($form->isSubmitted() && $form->isValid()) {
            /**
             * @var Announcement $announcement
             */
            $announcement = $form->getData();
            $em->flush();

            $this->addFlash('success',$this->translator->trans('Your changes have been updated.'));
            $data = $announcement;

            return $this->redirectToRoute('admin_list_announcements');
        }

        return $this->render('admin/edit_announcement.html.twig', [
            'form_template' => $form->createView(),
            'title' => $this->translator->trans('Manage Announcements'),
            'isEdit' => true
        ]);
    }

    /**
     * @Route("/admin/announcements/{slug}/remove", name="admin_announcements_remove", methods={"GET"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function remove_announcements($slug)
    {
        return $this->render('placeholder.html.twig', [
            'title' => 'Placeholder',
        ]);
    }

    /**
     * @Route("/admin/announcements/{slug}/pause", name="admin_announcements_pause", methods={"GET"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function pause_announcements($slug)
    {
        return $this->render('placeholder.html.twig', [
            'title' => 'Placeholder',
        ]);
    }

    /**
     * @Route("/admin/members", name="admin_list_members")
     * @IsGranted("ROLE_ADMIN")
     */
    public function list_members(Request $request, EntityManagerInterface $em, UserRepository $repository, PaginatorInterface $paginator)
    {
        return $this->render('admin/list_members_datatables.html.twig', [
            'title' => 'Members List'
        ]);

//        /**
//         * @var User $user
//         */
//        $user = $this->getUser();
//        $q = $request->query->get('q') ?: '';
//        $query = $repository->findAllBySquadron($user->getSquadron()->getId(), $q);
//
//        $size = $request->query->get('size') ?: '10';
//
//        $pagination = $paginator->paginate($query, $request->query->getInt('page', 1), $size);
//
//        return $this->render('admin/list_members_datatables.html.twig', [
//            'title' => 'Members List',
//            'pagination' => $pagination,
//            'query' => array_merge($request->request->all(), $request->query->all()),
//            'size' => $size
//        ]);

    }

    /**
     * @Route("/admin/members/{slug}/ban/{token}", name="admin_ban_member")
     * @IsGranted("ROLE_ADMIN")
     */
    public function ban_member($slug, $token, Request $request)
    {
        if (!$this->isCsrfTokenValid('new_announcement', $token)) {
            $this->addFlash('success', $this->translator->trans('Invalid CSRF Token. Please refresh the page to continue.'));
            return $this->redirectToRoute('admin_list_members');
        }

        return $this->redirectToRoute('admin_list_members');
    }

}
