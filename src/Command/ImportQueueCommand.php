<?php

namespace App\Command;

use App\Entity\ActivityCounter;
use App\Entity\Commander;
use App\Entity\EarningHistory;
use App\Entity\EarningType;
use App\Entity\User;
use App\Repository\ActivityCounterRepository;
use App\Repository\CommanderRepository;
use App\Repository\EarningHistoryRepository;
use App\Repository\EarningTypeRepository;
use App\Repository\ImportQueueRepository;
use App\Repository\RankRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class ImportQueueCommand extends Command
{
    protected static $defaultName = 'app:import-queue';
    /**
     * @var ImportQueueRepository
     */
    private $importQueueRepository;
    /**
     * @var UserRepository
     */
    private $userRepository;
    /**
     * @var CommanderRepository
     */
    private $commanderRepository;
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;
    /**
     * @var ParameterBagInterface
     */
    private $bag;
    /**
     * @var Commander $commander
     */
    private $commander;
    /**
     * @var User $user
     */
    private $user;
    /**
     * @var RankRepository
     */
    private $rankRepository;
    /**
     * @var EarningTypeRepository
     */
    private $earningTypeRepository;
    /**
     * @var EarningHistoryRepository
     */
    private $earningHistoryRepository;
    /**
     * @var ActivityCounterRepository
     */
    private $activityCounterRepository;

    private $group_code = ['Combat','Trade','Explore','Federation','Empire','CQC'];

    /**
     * @var EarningType $earning_type_obj
     */
    private $earning_type_obj;

    private $earning_type;
    /**
     * @var EarningHistory $earning_history
     */
    private $earning_history;
    /**
     * @var ActivityCounter $activity_counter
     */
    private $activity_counter;
    private $utc;

    public function __construct(ImportQueueRepository $importQueueRepository, UserRepository $userRepository, CommanderRepository $commanderRepository, EntityManagerInterface $entityManager, ParameterBagInterface $bag, RankRepository $rankRepository, EarningTypeRepository $earningTypeRepository, EarningHistoryRepository $earningHistoryRepository, ActivityCounterRepository $activityCounterRepository)
    {
        parent::__construct();
        $this->importQueueRepository = $importQueueRepository;
        $this->userRepository = $userRepository;
        $this->commanderRepository = $commanderRepository;
        $this->entityManager = $entityManager;
        $this->bag = $bag;
        $this->rankRepository = $rankRepository;
        $this->earningTypeRepository = $earningTypeRepository;
        $this->earningHistoryRepository = $earningHistoryRepository;
        $this->activityCounterRepository = $activityCounterRepository;
        $this->utc = new \DateTimeZone('UTC');

        $this->earning_type_obj = $this->earningTypeRepository->findAll();
        foreach($this->earning_type_obj as $i=>$row) {
            $this->earning_type[$row->getName()] = $row;
        }
    }

    protected function configure()
    {
        $this
            ->setDescription('Process import queue for parsing of Player Journal log files')
//            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
//            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $em = $this->entityManager;
        $folder_path = $this->bag->get('command.fileupload.path');
        $num_records = 0;

        if(!is_readable($folder_path) || !is_dir($folder_path)) {
            $io->error($folder_path . " is not readable or is not a directory.");
            return;
        }

//        $arg1 = $input->getArgument('arg1');
//
//        if ($arg1) {
//            $io->note(sprintf('You passed an argument: %s', $arg1));
//        }
//
//        if ($input->getOption('option1')) {
//            // ...
//        }

        $queue = $this->importQueueRepository->findBy([
            'progress_code' => 'Q'
        ],['game_datetime' => 'asc']);

        $max = count($queue);
        $progressBar = new ProgressBar($output, $max);
        $progressBar->start();

        foreach($queue as $i=>$entry) {
            $entry->setTimeStarted();
            $this->commander = $this->commanderRepository->findOneBy(['user' => $entry->getUser()]);
            $this->user = $entry->getUser();

            if(is_null($this->commander)) {
                $this->commander = new Commander();
                $this->commander->setUser($entry->getUser());
            }

            $file_path = sprintf("%s%s", $folder_path, $entry->getUploadFilename());
//            echo "working on " . $file_path . "\n";

            try {
                $fh = fopen($file_path, 'r');
            }
            catch (\Exception $e) {
                $io->error($e->getMessage());
            }

            if(!is_readable($file_path) || $fh === false) {
                $io->error($file_path . ' is not readable. Skipping.');
            }
            else {
                while (($data = fgets($fh)) !== false) {
                    $this->parseEntry($data);
                    time_nanosleep(0, 1000000);
                }
                $entry->setProgressCode('P');
                $em->persist($entry);
                $em->persist($this->commander);
                $em->flush();
            }
            time_nanosleep(0, 35000000);
            $progressBar->advance();
            $num_records++;
        }
        $progressBar->finish();
        $io->success(sprintf('%d Player Journal logs processed.', $num_records));
    }

    private function parseEntry($data) {
        $e = json_decode($data, true);
        $game_datetime = $e['timestamp'];

        switch ($e['event']) {
            case 'Fileheader':
                $this->activity_counter = $this->activityCounterRepository->findOneBy(['user' => $this->user, 'squadron' => $this->user->getSquadron(), 'activity_date' => new \DateTime($game_datetime, $this->utc)]);
                if(!is_object($this->activity_counter)) {
                    $this->activity_counter = new ActivityCounter();
                    $this->activity_counter->setUser($this->user)
                        ->setSquadron($this->user->getSquadron())
                        ->setActivityDate(new \DateTime($game_datetime, $this->utc));
                }
                $this->entityManager->persist($this->activity_counter);
                break;

            case 'LoadGame':
                $this->commander->setCredits($e['Credits']);
                $this->commander->setLoan($e['Loan']);
                break;

            case 'Commander':
                if(isset($e['FID'])) {
                    $this->commander->setPlayerId($e['FID']);
                }
                break;

            case 'Rank':
                foreach($this->group_code as $key) {
                    $rank = $this->rankRepository->findOneBy(['group_code' => strtolower($key), 'assigned_id' => $e[$key]]);
                    $this->commander->setRankId($key, $rank);
                }
                break;

            case 'Progress':
                foreach($this->group_code as $key) {
                    $this->commander->setRankProgress($key, $e[$key]);
                }
                break;

            case 'Statistics':
                $bank_acct = $e['Bank_Account'];
                $this->commander->setAsset($bank_acct['Current_Wealth']);
                break;

            case 'Bounty':
                $reward = isset($e['TotalReward']) ? $e['TotalReward']: $e['Reward'];
                $this->addEarningHistory($e['event'], $game_datetime, $reward);
                $this->activity_counter->addBountiesClaimed(1);
                break;

            case 'CapShipBond':
            case 'FactionKillBond':
                $this->addEarningHistory($e['event'], $game_datetime, $e['Reward']);
                $this->activity_counter->addBountiesClaimed(1);
                break;

            case 'MultiSellExplorationData':
                $num_systems = count($e['Discovered']);
                $num_bodies = 0;
                foreach($e['Discovered'] as $system) {
                    $num_bodies += $system['NumBodies'];
                }
                $crew_wage = $e['BaseValue'] + $e['Bonus'] - $e['TotalEarnings'];
                $this->addEarningHistory('ExplorationData', $game_datetime, $e['TotalEarnings'], $crew_wage);
                $this->activity_counter->addBodiesFound($num_bodies)
                    ->addSystemsScanned($num_systems);
                break;

            case 'SellExplorationData':
                $num_systems = count($e['Systems']);
                $num_bodies = count($e['Discovered']);

                if(isset($e['TotalEarnings'])) {
                    $crew_wage = $e['BaseValue'] + $e['Bonus'] - $e['TotalEarnings'];
                    $this->addEarningHistory('ExplorationData', $game_datetime, $e['TotalEarnings'], $crew_wage);
                }
                else {
                    $this->addEarningHistory('ExplorationData', $game_datetime, $e['BaseValue']+$e['Bonus']);
                }
                $this->activity_counter->addBodiesFound($num_bodies)
                    ->addSystemsScanned($num_systems);
                break;

            case 'SAAScanComplete':
                $efficiency = ($e['ProbesUsed'] <= $e['EfficiencyTarget']);
                $this->activity_counter->addSaaScanCompleted(1)
                    ->addEfficiencyAchieved($efficiency);
                break;

            case 'MarketBuy':
                $this->addEarningHistory($e['event'], $game_datetime, $e['TotalCost'] * -1);
                $this->activity_counter->addMarketBuy($e['Count']);
                break;

            case 'MarketSell':
                $this->addEarningHistory($e['event'], $game_datetime, $e['TotalSale']);
                $this->activity_counter->addMarketSell($e['Count']);
                if(isset($e['StolenGoods'])) {
                    $this->activity_counter->addStolenGoods($e['Count']);
                }
                break;

            case 'MiningRefined':
                $this->activity_counter->addMiningRefined(1);
                break;

            case 'CommunityGoalReward':
                $this->addEarningHistory($e['event'], $game_datetime, $e['Reward']);
                $this->activity_counter->addCgParticipated(1);
                break;

            case 'MissionCompleted':
                $name = isset($e['Name']) ? $e['Name'] : '';
                $pieces = explode('_',$name);
                $name = sprintf('%s_%s', ucfirst(strtolower($pieces[0])), $pieces[1]);
                $type = isset($this->earning_type[$name]) ? $name : $e['event'];
                $note = '';
                if($type == $e['event']) {
                    $note = $name;
                }
                if(isset($e['Reward'])) {
                    $this->addEarningHistory($type, $game_datetime, $e['Reward'], 0, $note);
                }
                $this->activity_counter->addMissionsCompleted(1);
                break;

            case 'CommitCrime':
                $this->activity_counter->addCrimesCommitted(1);
                break;

        }
    }

    private function addEarningHistory($type, $date, $reward, $crew_wage = 0, $notes = '') {
        $eh = new EarningHistory();
        $eh->setUser($this->user)
            ->setEarningType($this->earning_type[$type])
            ->setSquadron($this->user->getSquadron())
            ->setEarnedOn(new \DateTime($date, $this->utc))
            ->setReward($reward)
            ->setCrewWage($crew_wage);
        if($notes) {
            $eh->setNotes($notes);
        }
        $this->entityManager->persist($eh);
    }
}
