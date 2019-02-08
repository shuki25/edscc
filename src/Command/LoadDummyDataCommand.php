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

class LoadDummyDataCommand extends Command
{
    protected static $defaultName = 'app:load-dummy-data';

    /**
     * @var EntityManagerInterface
     */
    private $manager;

    /** @var Generator */
    private $faker;
    private $referencesIndexByORM = [];
    private $referencesRank = [];
    private $rank_list = ['combat','trade','explore','federation','empire','cqc'];
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

    public function __construct(UserPasswordEncoderInterface $passwordEncoder, SquadronRepository $squadronRepository, RankRepository $rankRepository, UserRepository $userRepository, FactionRepository $factionRepository, PowerRepository $powerRepository, PlatformRepository $platformRepository, StatusRepository $statusRepository, EntityManagerInterface $manager, EarningTypeRepository $earningTypeRepository)
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
    }

    protected function configure()
    {
        $this
            ->setDescription('Add dummy squadrons, accounts, daily earning and activities data to the database. [For development and testing only.]')
            ->addOption('users', 'u', InputOption::VALUE_REQUIRED, 'Number of users to create. (Default: 100)')
            ->addOption('squadrons', 's', InputOption::VALUE_REQUIRED, 'Number of Squadrons to create. (Default: 10')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $this->faker = Factory::create();

        $num_users = null !== $input->getOption('users') ? $input->getOption('users') : 100;
        $num_squads = null !=  $input->getOption('squadrons') ? $input->getOption('squadrons') : 10;
        $num_announcements = (30 * $num_squads) + $this->faker->numberBetween(15,75);

        $tot_num = ($num_users * 4) + $num_squads + $num_announcements;
        $section1 = $output->section();
        $section2 = $output->section();

        $progressBar = new ProgressBar($section1);
        $progressBar2 = new ProgressBar($section2);
        $progressBar->setFormat('very_verbose');
        $progressBar2->setFormat('very_verbose');
        $progressBar2->setRedrawFrequency(7);
        $progressBar->start($tot_num);
        $progressBar2->start(100);

        $this->referencesIndexByORM[Rank::class] = $this->rankRepository->findBy(['group_code' => 'service']);
        $this->referencesIndexByORM[Faction::class] = $this->factionRepository->findAll();
        $this->referencesIndexByORM[Power::class] = $this->powerRepository->findAll();
        $this->referencesIndexByORM[Platform::class] = $this->platformRepository->findAll();
        $this->referencesIndexByORM[Status::class] = $this->statusRepository->findAll();
        $this->referencesIndexByORM[EarningType::class] = $this->earningTypeRepository->findAll();

        $squadron = $this->squadronRepository->findOneBy(['id'=> 1]);
        $status_approved = $this->statusRepository->findOneBy(['name' => 'Approved']);

        $this->createMany(User::class, $num_users, function(User $user, $count) use ($squadron, $progressBar, $status_approved){
            $user->setEmail($this->faker->email)
                ->setCommanderName($this->faker->userName)
                ->setPassword($this->passwordEncoder->encodePassword($user, 'test123'))
                ->setAvatarUrl($this->faker->imageUrl(200,200))
                ->setSquadron($squadron)
                ->setApikey($this->faker->md5)
                ->setRank($this->getRandomReferenceByORM(Rank::class))
                ->setStatus($this->faker->optional(0.1, $status_approved)->randomElement($this->referencesIndexByORM[Status::class]))
                ->setDateJoined($this->faker->dateTimeBetween('-5 months', '-1 weeks'))
                ->setEmailVerify($this->faker->optional(0.1, 'Y')->randomElement(['Y','N']));
            $progressBar->advance();
        });

        $this->manager->flush();

        $this->referencesIndexByORM[User::class] = $this->userRepository->findBy([],null, 75, 1);
        $admins = $this->faker->randomElements($this->referencesIndexByORM[User::class], $num_squads);

        /**
         * @var User $user
         */
        foreach($admins as $user) {
            $user->setRoles(['ROLE_ADMIN']);
            $this->manager->persist($user);
        }

        $this->manager->flush();

        $this->createMany(Squadron::class, $num_squads, function(Squadron $squadron, $count) use ($admins, $progressBar) {
            $squadron->setName(ucwords($this->faker->words($this->faker->numberBetween(1,4), true)))
                ->setIdCode(strtoupper($this->faker->bothify('****')))
                ->setAdmin($admins[$count])
                ->setDescription($this->faker->sentences($this->faker->numberBetween(2,5),true))
                ->setWelcomeMessage($this->faker->paragraphs($this->faker->numberBetween(2,5),true))
                ->setFaction($this->getRandomReferenceByORM(Faction::class))
                ->setPower($this->getRandomReferenceByORM(Power::class))
                ->setHomeBase($this->faker->firstName . $this->faker->randomElement([' Memorial', ' Station', ' Base', ' Starport']))
                ->setPlatform($this->getRandomReferenceByORM(Platform::class));
            $progressBar->advance();
        });

        $this->manager->flush();

        $squadrons = $this->squadronRepository->findAll();
        array_shift($squadrons);  // Skip Unassigned entity

        $users = $this->userRepository->findAll();

        foreach ($this->rank_list as $key) {
            $this->referencesRank[$key] = $this->rankRepository->findBy(['group_code' => $key]);
        }

        foreach($users as $user) {
            $commander = new Commander();
            $credits = $this->faker->numberBetween(1000000,2000483600);
            $asset = $credits + $this->faker->numberBetween(10000000,147018040);
            $commander->setUser($user)
                ->setAsset($asset)
                ->setCredits($credits)
                ->setPlayerId('F' . $this->faker->randomNumber(5))
                ->setLoan($this->faker->optional(0.03, 0)->numberBetween(10000,1000000))
                ->setCombat($this->getRandomRankByGroupCode('combat'))
                ->setTrade($this->getRandomRankByGroupCode('trade'))
                ->setExplore($this->getRandomRankByGroupCode('explore'))
                ->setFederation($this->getRandomRankByGroupCode('federation'))
                ->setEmpire($this->getRandomRankByGroupCode('empire'))
                ->setCqc($this->getRandomRankByGroupCode('cqc'))
                ->setCombatProgress($this->faker->numberBetween(0,100))
                ->setTradeProgress($this->faker->numberBetween(0,100))
                ->setExploreProgress($this->faker->numberBetween(0,100))
                ->setFederationProgress($this->faker->numberBetween(0,100))
                ->setEmpireProgress($this->faker->numberBetween(0,100))
                ->setCqcProgress($this->faker->numberBetween(0,100));
            $user->setSquadron($this->faker->randomElement($squadrons));
            $this->manager->persist($commander);
            $this->manager->persist($user);
            $progressBar->advance();
        }

        $this->manager->flush();

        foreach($squadrons as $squadron) {
            $user = $squadron->getAdmin();
            if($user->getId()) {
                $user->setSquadron($squadron);
                $this->manager->persist($user);
            }
        }
        $this->manager->flush();

        foreach($users as $user) {

            $interval = $user->getDateJoined()->diff(new \DateTime('now'));
            $maxDays = round($interval->format('%a') * .65);
            $minDays = round($maxDays * .45);
            $num_dates = $this->faker->numberBetween($minDays,$maxDays);
            $min = $num_dates*5;
            $max = $num_dates*17;
            $num_earning = $this->faker->numberBetween($min,$max);

            $progressBar2->start($num_earning);
            $this->createMany(EarningHistory::class, $num_earning, function(EarningHistory $earningHistory, $count) use ($user, $progressBar2) {
                $reward = $this->faker->numberBetween(500,3000000) + $this->faker->optional(0.05, 0)->numberBetween(10000000,30000000);
                $type = $this->getRandomReferenceByORM(EarningType::class);
                if($type->getName() == 'MarketBuy') {
                    $reward *= -1;
                }
                $earningHistory->setUser($user)
                    ->setSquadron($user->getSquadron())
                    ->setEarningType($type)
                    ->setEarnedOn($this->faker->dateTimeBetween($user->getDateJoined(),'-1 hours'))
                    ->setReward($reward);
                $progressBar2->advance();
            });
            $progressBar2->finish();
            $progressBar->advance();

            $progressBar2->start($num_dates);

            $list_of_dates = [];

            for($i=0; $i < ($num_dates + 25); $i++) {
                $tmp = $this->faker->unique()->dateTimeBetween($user->getDateJoined(), '-1 hours');
                $list_of_dates[] = new \DateTime($tmp->format('Y-m-d'));
            }
            $list_of_dates = array_unique($list_of_dates, SORT_REGULAR);
            sort($list_of_dates);

            $num_in_list = count($list_of_dates) - 1;
            $num_dates = $num_dates > $num_in_list ? $num_in_list - 1 : $num_dates;

            $this->createMany(ActivityCounter::class, $num_dates, function (ActivityCounter $activityCounter, $count) use ($user, &$list_of_dates, $progressBar2) {
                $systemFound = $this->faker->optional(0.1, 0)->numberBetween(0,100);
                $bodiesFound = $systemFound ? ($systemFound * 8) + $this->faker->numberBetween(0,50) : 0;
                $ssaScanned = $bodiesFound ? round($bodiesFound * 0.04) + $this->faker->numberBetween(0,round($bodiesFound * 0.02)) : 0;
                $efficiency = $ssaScanned ? $ssaScanned - $this->faker->optional(0.2, 0)->numberBetween(0,round($ssaScanned * 0.15)) : 0;
                $marketbuy = $this->faker->optional(0.1, 0)->numberBetween(0,500);
                $marketsell = $marketbuy ? $marketbuy + $this->faker->numberBetween(0,10) : 0;

                $activityCounter->setUser($user)
                    ->setSquadron($user->getSquadron())
                    ->setActivityDate(next($list_of_dates))
                    ->addBountiesClaimed($this->faker->optional(0.1, 0)->numberBetween(0,25))
                    ->addSystemsScanned($systemFound)
                    ->addBodiesFound($bodiesFound)
                    ->addSaaScanCompleted($ssaScanned)
                    ->addEfficiencyAchieved($efficiency)
                    ->addMarketBuy($marketbuy)
                    ->addMarketSell($marketsell)
                    ->addMissionsCompleted($this->faker->optional(0.05, 0)->numberBetween(0,50))
                    ->addMiningRefined($this->faker->optional(0.05, 0)->numberBetween(0,200))
                    ->addStolenGoods($this->faker->optional(0.01, 0)->numberBetween(0,25))
                    ->addCgParticipated($this->faker->optional(0.1, 0)->numberBetween(0,2))
                    ->addCrimesCommitted($this->faker->optional(0.01, 0)->numberBetween(0,5));
                $progressBar2->advance();
            });
            $progressBar2->finish();
            $progressBar->advance();
        }

        $this->manager->flush();

        /**
         * @var User $authors[]
         */
        $authors = $this->faker->randomElements($this->referencesIndexByORM[User::class], 20);

        $this->createMany(Announcement::class, $num_announcements, function(Announcement $announcement, $count) use ($authors, $progressBar) {
            $announcement->setTitle($this->faker->sentence(10))
                ->setMessage($this->faker->paragraphs($this->faker->numberBetween(2,5),true))
                ->setSquadron($authors[$count%20]->getSquadron())
                ->setPublishAt($this->faker->dateTimeBetween('-6 months', '+1 months'))
                ->setCreatedAt($this->faker->dateTimeBetween('-6 months', '-1 seconds'))
                ->setUpdatedAt($this->faker->dateTimeBetween('-1 months', '-1 seconds'))
                ->setUpdatedBy($authors[$count%20])
                ->setCreatedBy($authors[$count%20])
                ->setUser($authors[$count%20]);
            $progressBar->advance();
        });

        $this->manager->flush();

        $io->success('Dummy Data has been generated.');
    }

    function createMany(string $className, int $count, callable $factory)
    {
        for($i=0; $i < $count; $i++) {
            $entity = new $className();
            $factory($entity, $i);

            $this->manager->persist($entity);
            if($i % 500 === 0) {
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
