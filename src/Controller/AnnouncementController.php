<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\AnnouncementRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class AnnouncementController extends AbstractController
{
    /**
     * @var User $user
     */
    private $user;

    /**
     * @Route("/announcements", name="show_announcements")
     * @IsGranted("ROLE_USER")
     */
    public function show_announcements(AnnouncementRepository $announcementRepository)
    {

        $this->user = $this->getUser();

        $articles = $announcementRepository->findAllbyPublishStatus($this->user->getSquadron()->getId());

        return $this->render('announcement/index.html.twig', [
            'articles' => $articles
        ]);
    }
}
