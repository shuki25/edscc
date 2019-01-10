<?php

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\RouterInterface;

class DashboardController extends AbstractController
{
    private $router;

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    /**
     * @Route("/", name="dashboard")
     * @IsGranted("ROLE_USER")
     */
    public function index()
    {
        $user = $this->getUser();

        $hash = md5(strtolower(trim($user->getUsername())));
        $gravatar_url = sprintf("https://www.gravatar.com/avatar/%s?d=mp",$hash);

        if($user->getGoogleFlag() === "Y") {
            $gravatar_url = $user->getAvatarUrl();
        }

        return $this->render('dashboard/index.html.twig', [
            'controller_name' => 'DashboardController',
            'title' => 'Squadron Command Center Dashboard',
            'description' => 'General Overview of Squadron Operations',
            'commander_name' => $user->getCommanderName(),
            'gravatar_url' => $gravatar_url,
            'date_created' => $user->getCreatedAt()
        ]);
    }
}
