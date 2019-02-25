<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\MotdType;
use App\Repository\AnnouncementRepository;
use App\Repository\MotdRepository;
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
    public function show_announcements(AnnouncementRepository $announcementRepository, MotdRepository $motdRepository)
    {
        $this->user = $this->getUser();

        $articles = $announcementRepository->findAllbyPublishStatus($this->user->getSquadron()->getId());
        $motd = $motdRepository->findBy(['show_flag' => true],['id' => 'desc']);

        return $this->render('announcement/index.html.twig', [
            'articles' => $articles,
            'motd' => $motd
        ]);
    }
}
