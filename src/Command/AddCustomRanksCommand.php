<?php

namespace App\Command;

use App\Entity\CustomRank;
use App\Repository\CustomRankRepository;
use App\Repository\RankRepository;
use App\Repository\SquadronRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class AddCustomRanksCommand extends Command
{
    protected static $defaultName = 'app:add-custom-ranks';
    /**
     * @var SquadronRepository
     */
    private $squadronRepository;
    /**
     * @var UserRepository
     */
    private $userRepository;
    /**
     * @var EntityManagerInterface
     */
    private $em;
    /**
     * @var RankRepository
     */
    private $rankRepository;
    /**
     * @var CustomRankRepository
     */
    private $customRankRepository;

    public function __construct(SquadronRepository $squadronRepository, UserRepository $userRepository, RankRepository $rankRepository, CustomRankRepository $customRankRepository, EntityManagerInterface $em)
    {
        parent::__construct();
        $this->squadronRepository = $squadronRepository;
        $this->userRepository = $userRepository;
        $this->em = $em;
        $this->rankRepository = $rankRepository;
        $this->customRankRepository = $customRankRepository;
    }

    protected function configure()
    {
        $this->setDescription('Upgrade database schema. Add a new table and a new column, populate missing data.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $squadrons = $this->squadronRepository->findAll();
        $max = count($squadrons);

        $users = $this->userRepository->findAll();
        $max += count($users);

        $progressBar = new ProgressBar($output, $max);
        $progressBar->setFormat('very_verbose');
        $progressBar->start();

        $ranks = $this->rankRepository->findBy(['group_code' => 'service'], ['assigned_id' => 'asc']);
        $rank_idx = [];
        $order_idx = [];

        foreach ($ranks as $rank) {
            $order_idx[$rank->getId()] = $rank->getAssignedId();
        }

        foreach ($squadrons as $squadron) {
            $custom_ranks = $squadron->getCustomRanks();
            if ($custom_ranks->isEmpty()) {
                foreach ($ranks as $row) {
                    $custom_rank = new CustomRank();
                    $custom_rank->setOrderId($row->getAssignedId())
                        ->setName($row->getName());
                    $this->em->persist($custom_rank);
                    $squadron->addCustomRank($custom_rank);
                }
                $this->em->flush();
                $custom_ranks = $squadron->getCustomRanks();
            }

            /**
             * @var CustomRank $element
             */
            $element = $custom_ranks->first();
            do {
                $rank_idx[$element->getSquadron()->getId()][$element->getOrderId()] = $element;
                $element = $custom_ranks->next();
            } while ($element);

            $progressBar->advance();
        }

        foreach ($users as $user) {
            $user->setCustomRank($rank_idx[$user->getSquadron()->getId()][$order_idx[$user->getRank()->getId()]]);
            $this->em->flush();
            $progressBar->advance();
        }

        $io->success('Database update completed.');
    }
}
