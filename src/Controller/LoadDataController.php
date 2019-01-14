<?php

namespace App\Controller;

use App\Entity\Announcement;
use App\Entity\Faction;
use App\Entity\Platform;
use App\Entity\Power;
use App\Entity\Rank;
use App\Entity\Squadron;
use App\Entity\User;
use App\Repository\FactionRepository;
use App\Repository\PlatformRepository;
use App\Repository\PowerRepository;
use App\Repository\RankRepository;
use App\Repository\SquadronRepository;
use App\Repository\UserRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class LoadDataController extends AbstractController
{
    /** @var ObjectManager */
    private $manager;

    /** @var Generator */
    private $faker;
    private $referencesIndexByORM = [];

    /**
     * @Route("/load/data", name="load_data")
     */
    public function index(UserPasswordEncoderInterface $passwordEncoder, SquadronRepository $squadronRepository, RankRepository $rankRepository, UserRepository $userRepository, FactionRepository $factionRepository, PowerRepository $powerRepository, PlatformRepository $platformRepository)
    {

        $this->manager = $this->getDoctrine()->getManager();
        $this->faker = Factory::create();

        $this->referencesIndexByORM[Rank::class] = $rankRepository->findAll();
        $this->referencesIndexByORM[Faction::class] = $factionRepository->findAll();
        $this->referencesIndexByORM[Power::class] = $powerRepository->findAll();
        $this->referencesIndexByORM[Platform::class] = $platformRepository->findAll();

        $squadron = $squadronRepository->findOneBy(['id'=> 1]);

        $this->createMany(User::class, 200, function(User $user, $count) use ($squadron, $passwordEncoder){
            $user->setEmail($this->faker->email)
                ->setCommanderName($this->faker->userName)
                ->setPassword($passwordEncoder->encodePassword($user, 'test123'))
                ->setAvatarUrl($this->faker->imageUrl(200,200))
                ->setSquadron($squadron)
                ->setApikey($this->faker->md5)
                ->setRank($this->getRandomReferenceByORM(Rank::class))
                ->setEmailVerify($this->faker->optional(0.1, 'Y')->randomElement(['Y','N']));
        });

        $this->manager->flush();

        $this->referencesIndexByORM[User::class] = $userRepository->findBy([],null, 75);
        $admins = $this->faker->randomElements($this->referencesIndexByORM[User::class], 15);

        /**
         * @var User $user
         */
        foreach($admins as $user) {
            $user->setRoles(['ROLE_ADMIN']);
            $this->manager->persist($user);
        }

        $this->manager->flush();

        $this->createMany(Squadron::class, 15, function(Squadron $squadron, $count) use ($admins) {
            $squadron->setName(ucwords($this->faker->words($this->faker->numberBetween(1,4), true)))
                ->setIdCode(strtoupper($this->faker->bothify('****')))
                ->setAdmin($admins[$count])
                ->setDescription($this->faker->sentences($this->faker->numberBetween(2,5),true))
                ->setWelcomeMessage($this->faker->paragraphs($this->faker->numberBetween(2,5),true))
                ->setFaction($this->getRandomReferenceByORM(Faction::class))
                ->setPower($this->getRandomReferenceByORM(Power::class))
                ->setHomeBase($this->faker->firstName . $this->faker->randomElement([' Memorial', ' Station', ' Base', ' Starport']))
                ->setPlatform($this->getRandomReferenceByORM(Platform::class));
        });

        $this->manager->flush();

        $squadrons = $squadronRepository->findAll();
        array_shift($squadrons);  // Skip Unassigned entity

        $users = $userRepository->findAll();

        foreach($users as $user) {
            $user->setSquadron($this->faker->randomElement($squadrons));
            $this->manager->persist($user);
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
        $authors = [];

        $authors = $this->faker->randomElements($this->referencesIndexByORM[User::class], 20);

        $this->createMany(Announcement::class, 350, function(Announcement $announcement, $count) use ($authors) {
            $announcement->setTitle($this->faker->sentence(10))
                ->setMessage($this->faker->paragraphs($this->faker->numberBetween(2,5),true))
                ->setSquadron($authors[$count%20]->getSquadron())
                ->setPublishAt($this->faker->dateTimeBetween('-6 months', '+1 months'))
                ->setCreatedAt($this->faker->dateTimeBetween('-6 months', '-1 seconds'))
                ->setUpdatedAt($this->faker->dateTimeBetween('-1 months', '-1 seconds'))
                ->setUpdatedBy($authors[$count%20])
                ->setCreatedBy($authors[$count%20])
                ->setUser($authors[$count%20]);
        });

        $this->manager->flush();

        $this->addFlash('success', 'Dummy data successfully loaded.');

        return $this->redirectToRoute('app_logout');
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
}
