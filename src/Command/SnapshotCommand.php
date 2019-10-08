<?php

namespace App\Command;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\ErrorLogHelper;
use App\Service\SnapshotHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class SnapshotCommand extends Command
{
    protected static $defaultName = 'app:snapshot';

    private $utc;

    /**
     * @var SnapshotHelper
     */
    private $snapshotHelper;
    /**
     * @var ErrorLogHelper
     */
    private $errorLogHelper;
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;
    /**
     * @var UserRepository
     */
    private $userRepository;

    public function __construct(SnapshotHelper $snapshotHelper, EntityManagerInterface $entityManager, ErrorLogHelper $errorLogHelper, UserRepository $userRepository)
    {
        parent::__construct();

        $this->snapshotHelper = $snapshotHelper;
        $this->errorLogHelper = $errorLogHelper;
        $this->entityManager = $entityManager;
        $this->userRepository = $userRepository;
        $this->utc = new \DateTimeZone('utc');
    }

    protected function configure()
    {
        $this
            ->setDescription("Create snapshot archive for statistic reporting and summaries.  Please Specify\n  the time frame to capture.")
            ->addOption('daily', 'd', InputOption::VALUE_OPTIONAL, 'Daily [YYYY-MM-DD]', false)
            ->addOption('weekly', 'w', InputOption::VALUE_OPTIONAL, 'Weekly [Week number]', false)
            ->addOption('monthly', 'm', InputOption::VALUE_OPTIONAL, 'Monthly [YYYY-MM]', false)
            ->addOption('yearly', 'y', InputOption::VALUE_OPTIONAL, 'Yearly [YYYY]', false)
            ->addOption('user', 'u', InputOption::VALUE_REQUIRED, 'User [Login email]');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $options = $input->getOptions();

        $max = 100;
        $entry = 100;

        $section1 = $output->section();
        $section2 = $output->section();
        $userBar = new ProgressBar($section1, $max);
        $snapshotBar = new ProgressBar($section2);
        $userBar->setFormat('very_verbose');
        $snapshotBar->setFormat('very_verbose');
        $userBar->start();
        $snapshotBar->start();

        $start_time = microtime(true);
        $userBar->advance();
        $snapshotBar->advance();

        /**
         * @var User $prev_user
         */
        $prev_user = null;

        if ($input->getOption('daily') !== false) {
            echo "daily\n";
        } elseif ($input->getOption('weekly') !== false) {
            echo "weekly\n";
        } elseif ($input->getOption('monthly') !== false) {
            echo "monthly\n";
        } elseif ($input->getOption('yearly') !== false) {
            echo "yearly\n";
        }
        dump($options);

        $io->success('You have a new command! Now make it your own! Pass --help to see your options.');
    }
}
