<?php

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\RouterInterface;

/**
 *
 * @IsGranted("IS_AUTHENTICATED_FULLY")
 */

class ProfileController extends BaseController
{
    private $router;

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    /**
     * @Route("/profile", name="app_profile")
     */
    public function index()
    {
        $user = $this->getUser();

        $hash = md5(strtolower(trim($user->getUsername())));
        $gravatar_url = sprintf("https://www.gravatar.com/avatar/%s?d=mp",$hash);

        if($user->getGoogleFlag() === "Y") {
            $gravatar_url = $user->getAvatarUrl();
        }

        return $this->render('profile/index.html.twig', [
            'controller_name' => 'DashboardController',
            'title' => 'Commander Profile',
            'description' => '',
            'commander_name' => $user->getCommanderName(),
            'gravatar_url' => $gravatar_url,
            'date_created' => $user->getCreatedAt()
        ]);
    }
}
