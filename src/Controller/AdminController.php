<?php

namespace App\Controller;

use App\Entity\Announcement;
use App\Entity\CustomRank;
use App\Entity\Squadron;
use App\Entity\User;
use App\Form\AnnouncementType;
use App\Form\SquadronType;
use App\Repository\AclRepository;
use App\Repository\AnnouncementRepository;
use App\Repository\CustomRankRepository;
use App\Repository\RankRepository;
use App\Repository\SquadronRepository;
use App\Repository\StatusRepository;
use App\Repository\TagsRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
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
    private $utc;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
        $this->utc = new \DateTimeZone('UTC');
    }

    /**
     * @Route("/admin/squadron_settings", name="admin_squadron_settings")
     * @IsGranted("ROLE_ADMIN")
     */
    public function squadronSettings(Request $request, EntityManagerInterface $em, SquadronRepository $squadronRepository, TagsRepository $tagsRepository, RankRepository $rankRepository)
    {
        /**
         * @var User $user
         */
        $user = $this->getUser();
        $squadron = $user->getSquadron();
        $em = $this->getDoctrine()->getManager();
        $tags_bank = [];
        $tags = $tagsRepository->findBy([], ['group_code' => 'asc', 'name' => 'asc']);
        $group_code = [
            'activities' => "Activities",
            'platform' => "Platform",
            'availability' => "Availability",
            'game_mode' => "Game Mode",
            'play_style' => "Play Style",
            'language' => "Language",
            'attitude' => "Attitude",
        ];
        $squadron_tags = $user->getSquadron()->getSquadronTags();
        foreach ($squadron_tags as $i => $row) {
            $tags_bank[] = $row->getTag()->getId();
        }

        $custom_ranks = $user->getSquadron()->getCustomRanks();
        $ranks = $rankRepository->findBy(['group_code' => 'squadron'], ['assigned_id' => 'asc']);

        if ($custom_ranks->isEmpty()) {
            foreach ($ranks as $i => $row) {
                $custom_rank = new CustomRank();
                $custom_rank->setOrderId($row->getAssignedId())
                    ->setName($row->getName());
                $em->persist($custom_rank);
                $squadron->addCustomRank($custom_rank);
            }
            $em->flush();
            $custom_ranks = $user->getSquadron()->getCustomRanks();
        }

        $custom_ranks_idx = [];

        foreach ($custom_ranks as $i => $row) {
            $custom_ranks_idx[$row->getOrderId()] = $row->getName();
        }

        /**
         * @var Squadron data
         */
        $data = $squadronRepository->findOneBy(['id' => $user->getSquadron()->getId()]);
//        dd($squad);
        $form = $this->createForm(SquadronType::class, $data);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            /**
             * @var Squadron $squad
             */
            $squad = $form->getData();
            $em->flush();

            $this->addFlash('success', $this->translator->trans('Your settings have been saved.'));
            $data = $squad;
        }

        return $this->render('admin/squadron_settings.html.twig', [
            'form_template' => $form->createView(),
            'title' => 'Squadron Settings',
            'tags' => $tags,
            'group_code' => $group_code,
            'tags_bank' => $tags_bank,
            'ranks' => $ranks,
            'custom_ranks_idx' => $custom_ranks_idx,
            'squad' => $data
        ]);
    }

    /**
     * @Route("/admin/squadron_settings/update_ranks", name="admin_update_ranks", methods={"POST"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function adminUpdateRanks(Request $request, CustomRankRepository $customRankRepository)
    {
        $token = $request->request->get('_token');

        /**
         * @var User $user
         */
        $user = $this->getUser();
        $squadron = $user->getSquadron();
        $custom_ranks = $squadron->getCustomRanks();


        if (!$this->isCsrfTokenValid('update_ranks', $token)) {
            $this->addFlash('success', $this->translator->trans('Expired CSRF Token. Please refresh the page to continue.'));
        } else {
            $em = $this->getDoctrine()->getManager();
            $new_ranks = $request->request->get('new_rank');
            /**
             * @var CustomRank $element
             */
            $element = $custom_ranks->first();

            do {
                $element->setName($new_ranks[$element->getOrderId()]);
                $element = $custom_ranks->next();
            } while (is_object($element));
            $em->flush();
            $this->addFlash('success', $this->translator->trans('Squadron Rank Classifications Saved.'));
        }

        return $this->redirectToRoute('admin_squadron_settings');
    }

    /**
     * @Route("/admin/announcements", name="admin_list_announcements")
     * @IsGranted("ROLE_EDITOR")
     */
    public function listAnnouncements()
    {
        return $this->render('admin/list_announcements_datatables.html.twig', [
            'title' => 'Members List'
        ]);
    }

    /**
     * @Route("/admin/announcements/new/{token}", name="admin_announcements_new", methods={"GET","POST"})
     * @IsGranted("ROLE_EDITOR")
     */
    public function newAnnouncements($token, Request $request, EntityManagerInterface $em)
    {
        /**
         * @var User $user
         */
        $user = $this->getUser();

        /**
         * @var Announcement $data
         */
        $data = new Announcement();
        $data->setUser($user)->setSquadron($user->getSquadron());

        $form = $this->createForm(AnnouncementType::class, $data);
        $form->handleRequest($request);

        if (!$this->isCsrfTokenValid('new_announcement', $token)) {
            $this->addFlash('success', $this->translator->trans('Expired CSRF Token. Please refresh the page to continue.'));
            return $this->redirectToRoute('admin_list_announcements');
        }

        if ($form->isSubmitted() && $form->isValid()) {
            /**
             * @var Announcement $announcement
             */
            $announcement = $form->getData();
            $em->persist($announcement);
            $em->flush();

            $this->addFlash('success', $this->translator->trans('New announcement has been added.'));
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
     * @IsGranted("ROLE_EDITOR")
     */
    public function editAnnouncements($slug, $token, Request $request, EntityManagerInterface $em, AnnouncementRepository $repository)
    {
        /**
         * @var User $user
         */
        $user = $this->getUser();

        $data = $repository->findOneBy(['id' => $slug, 'squadron' => $user->getSquadron()->getId()]);

        if (!is_object($data)) {
            $this->addFlash('alert', $this->translator->trans('Permission Denied. Unable to access to this resource.'));
            return $this->redirectToRoute('admin_list_announcements');
        }
        $form = $this->createForm(AnnouncementType::class, $data);
        $form->handleRequest($request);

        if (!$this->isCsrfTokenValid('edit_announcement', $token)) {
            $this->addFlash('success', $this->translator->trans('Expired CSRF Token. Please refresh the page to continue.'));
            return $this->redirectToRoute('admin_list_announcements');
        }

        if ($request->request->get('cancel')) {
            $this->addFlash('alert', $this->translator->trans('Changes were not saved.'));
            return $this->redirectToRoute('admin_list_announcements');
        }

        if ($form->isSubmitted() && $form->isValid()) {
            /**
             * @var Announcement $announcement
             */
            $announcement = $form->getData();
            $em->flush();

            $this->addFlash('success', $this->translator->trans('Your changes have been updated.'));
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
     * @Route("/admin/announcements/{slug}/pin/{token}", name="admin_announcement_pin", methods={"GET"})
     * @IsGranted("ROLE_EDITOR")
     */
    public function pinAnnouncement($slug, $token)
    {
        return $this->render('placeholder.html.twig', [
            'title' => 'Placeholder',
        ]);
    }

    /**
     * @Route("/admin/announcements/{slug}/remove/{token}", name="admin_announcement_remove", methods={"GET"})
     * @IsGranted("ROLE_EDITOR")
     */
    public function removeAnnouncement($slug, $token)
    {
        return $this->render('placeholder.html.twig', [
            'title' => 'Placeholder',
        ]);
    }

    /**
     * @Route("/admin/announcements/{slug}/hide", name="admin_announcement_hide", methods={"GET"})
     * @IsGranted("ROLE_EDITOR")
     */
    public function hideAnnouncement($slug, $token)
    {
        return $this->render('placeholder.html.twig', [
            'title' => 'Placeholder',
        ]);
    }

    /**
     * @Route("/admin/members", name="admin_list_members")
     */
    public function listMembers(Request $request)
    {
        $this->denyAccessUnlessGranted(['CAN_CHANGE_STATUS', 'CAN_EDIT_USER', 'CAN_EDIT_PERMISSIONS', 'CAN_VIEW_HISTORY'], User::class);

        return $this->render('admin/list_members_datatables.html.twig', [
            'title' => 'Members List'
        ]);
    }

    /**
     * @Route("/admin/members/edit/{id}/{token}", name="admin_edit_member")
     */
    public function editMember($id, $token, UserRepository $userRepository, CustomRankRepository $customRankRepository, StatusRepository $statusRepository, AclRepository $aclRepository)
    {
        $squadron_id = $this->getUser()->getSquadron()->getId();
        $user = $userRepository->findOneBy(['id' => $id, 'Squadron' => $squadron_id]);

        if ($id == $this->getUser()->getId()) {
            $this->denyAccessUnlessGranted('CAN_MODIFY_SELF');
        }
        $this->denyAccessUnlessGranted(['CAN_EDIT_USER', 'CAN_EDIT_PERMISSIONS', 'CAN_VIEW_HISTORY'], $user);

        $ranks = $customRankRepository->findBy(['squadron' => $squadron_id], ['order_id' => 'asc']);
        $statuses = $statusRepository->findBy([], ['name' => 'asc'], 5);
        if ($this->isGranted("ROLE_ADMIN")) {
            $acls = $aclRepository->findBy([], ['list_order' => 'asc']);
        } else {
            $acls = $aclRepository->findBy(['admin_flag' => false], ['list_order' => 'asc']);
        }

        if (!$this->isCsrfTokenValid('edit_member', $token)) {
            $this->addFlash('alert', $this->translator->trans('Expired CSRF Token. Please refresh the page to continue.'));
            return $this->redirectToRoute('admin_list_members');
        }


        return $this->render('admin/edit_member.html.twig', [
            'title' => 'Editing squadron member',
            'user' => $user,
            'ranks' => $ranks,
            'statuses' => $statuses,
            'acls' => $acls
        ]);
    }

    /**
     * @Route("/admin/members/save", name="admin_save_member", methods={"POST"})
     */
    public function saveMember(Request $request, UserRepository $userRepository, RankRepository $rankRepository, CustomRankRepository $customRankRepository, StatusRepository $statusRepository)
    {
        $id = $request->request->get('id');

        if ($id == $this->getUser()->getId()) {
            $this->denyAccessUnlessGranted('CAN_MODIFY_SELF');
        }
        $this->denyAccessUnlessGranted(['CAN_EDIT_USER', 'CAN_EDIT_PERMISSIONS']);

        $em = $this->getDoctrine()->getManager();
        $squadron_id = $this->getUser()->getSquadron()->getId();
        $token = $request->request->get('_token');
        $data = $request->request->all();
        $welcome_flag = isset($data['welcome_message_flag']) ? "Y" : "N";
        $email_flag = isset($data['email_verify']) ? "Y" : "N";
        $join_date = isset($data['join_date']) ? new \DateTime($data['join_date'], $this->utc) : new \DateTime('new', $this->utc);

        if (!$this->isCsrfTokenValid('save_member', $token)) {
            $this->addFlash('alert', $this->translator->trans('Expired CSRF Token. Please refresh the page to continue.'));
            return $this->redirectToRoute('admin_list_members');
        }

        $user = $userRepository->findOneBy(['id' => $id, 'Squadron' => $squadron_id]);

        if ($this->isGranted('CAN_EDIT_USER')) {
            $status = $statusRepository->findOneBy(['id' => $data['status_id']]);
            $rank = $rankRepository->findOneBy(['group_code' => 'squadron', 'assigned_id' => $data['rank_id']]);
            $custom_rank = $customRankRepository->findOneBy(['squadron' => $squadron_id, 'order_id' => $data['rank_id']]);
            if (is_object($user)) {
                $user->setStatus($status)->setRank($rank)->setCustomRank($custom_rank)->setWelcomeMessageFlag($welcome_flag)->setEmailVerify($email_flag)->setDateJoined($join_date);
                $em->flush();
            }
        }

        if ($this->isGranted('CAN_EDIT_PERMISSIONS')) {
            if (is_object($user)) {
                $acl = isset($data['acl']) ? $data['acl'] : [];

                $roles = $user->getRoles();
                if (in_array("ROLE_SUPERUSER", $roles)) {
                    $acl[] = "ROLE_SUPERUSER";
                }
                $user->setRoles($acl);
                $em->flush();
            }
        }

        $this->addFlash('success', $this->translator->trans('Your changes have been updated.'));
        return $this->redirectToRoute('admin_list_members');
    }
}
