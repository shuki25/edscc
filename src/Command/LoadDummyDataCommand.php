<?php

namespace App\Command;

use App\Entity\Announcement;
use App\Entity\Commander;
use App\Entity\Faction;
use App\Entity\Platform;
use App\Entity\Power;
use App\Entity\Rank;
use App\Entity\Squadron;
use App\Entity\Status;
use App\Entity\User;
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

    public function __construct(UserPasswordEncoderInterface $passwordEncoder, SquadronRepository $squadronRepository, RankRepository $rankRepository, UserRepository $userRepository, FactionRepository $factionRepository, PowerRepository $powerRepository, PlatformRepository $platformRepository, StatusRepository $statusRepository, EntityManagerInterface $manager)
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
    }

    protected function configure()
    {
        $this
            ->setDescription('Add a short description for your command')
            ->addOption('users', 'u', InputOption::VALUE_REQUIRED, 'Number of users to create. (Default: 100)')
            ->addOption('squadrons', 's', InputOption::VALUE_REQUIRED, 'Number of Squadrons to create. (Default: 10')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $num_users = null !== $input->getOption('users') ? $input->getOption('users') : 100;
        $num_squads = null !=  $input->getOption('squadrons') ? $input->getOption('squadrons') : 10;
        $num_announcements = 30 * $num_squads;

        $tot_num = ($num_users * 2) + $num_squads + $num_announcements;

        $progressBar = new ProgressBar($output, $tot_num);
        $progressBar->start();

        $this->faker = Factory::create();

        $this->referencesIndexByORM[Rank::class] = $this->rankRepository->findBy(['group_code' => 'service']);
        $this->referencesIndexByORM[Faction::class] = $this->factionRepository->findAll();
        $this->referencesIndexByORM[Power::class] = $this->powerRepository->findAll();
        $this->referencesIndexByORM[Platform::class] = $this->platformRepository->findAll();
        $this->referencesIndexByORM[Status::class] = $this->statusRepository->findAll();

        $squadron = $this->squadronRepository->findOneBy(['id'=> 1]);

        $this->createMany(User::class, $num_users, function(User $user, $count) use ($squadron, $progressBar){
            $user->setEmail($this->faker->email)
                ->setCommanderName($this->faker->userName)
                ->setPassword($this->passwordEncoder->encodePassword($user, 'test123'))
                ->setAvatarUrl($this->faker->imageUrl(200,200))
                ->setSquadron($squadron)
                ->setApikey($this->faker->md5)
                ->setRank($this->getRandomReferenceByORM(Rank::class))
                ->setStatus($this->getRandomReferenceByORM(Status::class))
                ->setDateJoined($this->faker->dateTimeBetween('-6 months', '-1 seconds'))
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
                ->setLoan($this->faker->optional(0.1, 0)->numberBetween(10000,1000000))
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
