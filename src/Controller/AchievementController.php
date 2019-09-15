<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\AchievementRuleRepository;
use App\Service\CommanderDataHelper;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class AchievementController extends AbstractController
{
    /**
     * @Route("/achievement", name="achievement")
     */
    public function index(CommanderDataHelper $dataHelper, AchievementRuleRepository $achievementRuleRepository)
    {
        /**
         * @var User $user
         */
        $user = $this->getUser();

        $dataHelper->checkForNewAchievements($user);
        $commander_data = $dataHelper->getData($user);
        $unlocked_achievements = $user->getAchievements()->toArray();
        $locked_achievements = $achievementRuleRepository->findLockedAchievements($user->getId());

        dump($commander_data);
        dump($locked_achievements);

        return $this->render('achievement/index.html.twig', [
            'controller_name' => 'AchievementController',
            'commander_data' => $commander_data,
            'unlocked_achievements' => $unlocked_achievements,
            'locked_achievements' => $locked_achievements
        ]);
    }
}
