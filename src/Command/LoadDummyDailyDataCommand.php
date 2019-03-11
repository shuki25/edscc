<?php

namespace App\Command;

use App\Entity\ActivityCounter;
use App\Entity\Announcement;
use App\Entity\Commander;
use App\Entity\EarningHistory;
use App\Entity\EarningType;
use App\Entity\Faction;
use App\Entity\Platform;
use App\Entity\Power;
use App\Entity\Rank;
use App\Entity\Squadron;
use App\Entity\Status;
use App\Entity\User;
use App\Repository\ActivityCounterRepository;
use App\Repository\EarningTypeRepository;
use App\Repository\FactionRepository;
use App\Repository\PlatformRepository;
use App\Repository\PowerRepository;
use App\Repository\RankRepository;
use App\Repository\SquadronRepository;
use App\Repository\StatusRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Faker\Factory;
use Faker\Generator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\ConsoleSectionOutput;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class LoadDummyDailyDataCommand extends Command
{
    protected static $defaultName = 'app:load-dummy-daily-data';

    /**
     * @var EntityManagerInterface
     */
    private $manager;

    /** @var Generator */
    private $faker;
    private $referencesIndexByORM = [];
    private $referencesRank = [];
    private $rankList = ['combat', 'trade', 'explore', 'federation', 'empire', 'cqc'];
    private $dateToUse;
    private $utc;

    /**
     * @var UserPasswordEncoderInterface
     */
    private $passwordEncoder;
    /**
     * @var SquadronRepository
     */
    private $squadronRepository;
    /**
     * @var RankRepository
     */
    private $rankRepository;
    /**
     * @var UserRepository
     */
    private $userRepository;
    /**
     * @var FactionRepository
     */
    private $factionRepository;
    /**
     * @var PowerRepository
     */
    private $powerRepository;
    /**
     * @var PlatformRepository
     */
    private $platformRepository;
    /**
     * @var StatusRepository
     */
    private $statusRepository;
    /**
     * @var EarningTypeRepository
     */
    private $earningTypeRepository;
    /**
     * @var ActivityCounterRepository
     */
    private $activityCounterRepository;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder, SquadronRepository $squadronRepository, RankRepository $rankRepository, UserRepository $userRepository, FactionRepository $factionRepository, PowerRepository $powerRepository, PlatformRepository $platformRepository, StatusRepository $statusRepository, EntityManagerInterface $manager, EarningTypeRepository $earningTypeRepository, ActivityCounterRepository $activityCounterRepository)
    {
        parent::__construct();

        $this->passwordEncoder = $passwordEncoder;
        $this->squadronRepository = $squadronRepository;
        $this->rankRepository = $rankRepository;
        $this->userRepository = $userRepository;
        $this->factionRepository = $factionRepository;
        $this->powerRepository = $powerRepository;
        $this->platformRepository = $platformRepository;
        $this->statusRepository = $statusRepository;
        $this->manager = $manager;
        $this->earningTypeRepository = $earningTypeRepository;
        $this->activityCounterRepository = $activityCounterRepository;
        $this->utc = new \DateTimeZone('UTC');
    }

    protected function configure()
    {
        $this
            ->setDescription('Add dummy daily earning and activities data to the database. [For development and testing only.]')
            ->addOption('date', 'd', InputOption::VALUE_REQUIRED, 'Date to use for fake data.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $this->faker = Factory::create();

        $this->dateToUse = null !== $input->getOption('date') ? new \DateTime($input->getOption('date'), $this->utc) : new \DateTime('now', $this->utc);

        $section1 = $output->section();
        $progressBar = new ProgressBar($section1);
        $progressBar->setFormat('very_verbose');

        $this->referencesIndexByORM[Rank::class] = $this->rankRepository->findBy(['group_code' => 'service']);
        $this->referencesIndexByORM[Faction::class] = $this->factionRepository->findAll();
        $this->referencesIndexByORM[Power::class] = $this->powerRepository->findAll();
        $this->referencesIndexByORM[Platform::class] = $this->platformRepository->findAll();
        $this->referencesIndexByORM[Status::class] = $this->statusRepository->findAll();
        $this->referencesIndexByORM[EarningType::class] = $this->earningTypeRepository->findAll();
        $this->referencesIndexByORM[User::class] = $this->userRepository->findBy([], null, null, 1);

        $num_users = count($this->referencesIndexByORM[User::class]);
        $min = round($num_users * .3);
        $max = round($num_users * .6);
        $num_users_to_update = $this->faker->numberBetween($min, $max);

        $progressBar->start($num_users_to_update);

        for ($i = 0; $i < $num_users_to_update; $i++) {

            /**
             * @var User $user
             */
            $user = $this->getRandomReferenceByORM(User::class);
            $num_earning = $this->faker->numberBetween(5, 15);

            $this->createMany(EarningHistory::class, $num_earning, function (EarningHistory $earningHistory, $count) use ($user) {
                $reward = $this->faker->numberBetween(500, 3000000) + $this->faker->optional(0.05, 0)->numberBetween(10000000, 30000000);
                $type = $this->getRandomReferenceByORM(EarningType::class);
                if ($type->getName() == 'MarketBuy') {
                    $reward *= -1;
                }
                $earningHistory->setUser($user)
                    ->setSquadron($user->getSquadron())
                    ->setEarningType($type)
                    ->setEarnedOn($this->dateToUse)
                    ->setReward($reward);
            });

            $activityCounter = $this->activityCounterRepository->findOneBy(['user' => $user, 'activity_date' => $this->dateToUse]);
            if (!is_object($activityCounter)) {
                $activityCounter = new ActivityCounter();
                $activityCounter->setUser($user)
                    ->setSquadron($user->getSquadron())
                    ->setActivityDate($this->dateToUse);
                $this->manager->persist($activityCounter);
            }

            $systemFound = $this->faker->optional(0.1, 0)->numberBetween(0, 100);
            $bodiesFound = $systemFound ? ($systemFound * 8) + $this->faker->numberBetween(0, 50) : 0;
            $ssaScanned = $bodiesFound ? round($bodiesFound * 0.04) + $this->faker->numberBetween(0, round($bodiesFound * 0.02)) : 0;
            $efficiency = $ssaScanned ? $ssaScanned - $this->faker->optional(0.2, 0)->numberBetween(0, round($ssaScanned * 0.15)) : 0;
            $marketbuy = $this->faker->optional(0.1, 0)->numberBetween(0, 500);
            $marketsell = $marketbuy ? $marketbuy + $this->faker->numberBetween(0, 10) : 0;

            $activityCounter->setActivityDate($this->dateToUse)
                ->addBountiesClaimed($this->faker->optional(0.1, 0)->numberBetween(0, 25))
                ->addSystemsScanned($systemFound)
                ->addBodiesFound($bodiesFound)
                ->addSaaScanCompleted($ssaScanned)
                ->addEfficiencyAchieved($efficiency)
                ->addMarketBuy($marketbuy)
                ->addMarketSell($marketsell)
                ->addMissionsCompleted($this->faker->optional(0.05, 0)->numberBetween(0, 50))
                ->addMiningRefined($this->faker->optional(0.05, 0)->numberBetween(0, 200))
                ->addStolenGoods($this->faker->optional(0.01, 0)->numberBetween(0, 25))
                ->addCgParticipated($this->faker->optional(0.1, 0)->numberBetween(0, 2))
                ->addCrimesCommitted($this->faker->optional(0.01, 0)->numberBetween(0, 5));

            $progressBar->advance();
        }

        $this->manager->flush();
        $progressBar->finish();

        $io->success('Dummy Daily Data has been generated.');
    }

    function createMany(string $className, int $count, callable $factory)
    {
        for ($i = 0; $i < $count; $i++) {
            $entity = new $className();
            $factory($entity, $i);

            $this->manager->persist($entity);
            if ($i % 500 === 0) {
                $this->manager->flush();
            }
        }
    }

    function getRandomReferenceByORM(string $className)
    {
        $randomReferenceKey = $this->faker->randomElement($this->referencesIndexByORM[$className]);

        return $randomReferenceKey;
    }

    function getRandomRankByGroupCode(string $group_code)
    {
        $randomReferenceKey = $this->faker->randomElement($this->referencesRank[$group_code]);

        return $randomReferenceKey;
    }
}
