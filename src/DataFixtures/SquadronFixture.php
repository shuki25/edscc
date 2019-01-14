<?php

namespace App\DataFixtures;

use App\Entity\Faction;
use App\Entity\Platform;
use App\Entity\Power;
use App\Entity\Squadron;
use App\Entity\User;
use App\Repository\FactionRepository;
use App\Repository\PlatformRepository;
use App\Repository\PowerRepository;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class SquadronFixture extends BaseFixture implements DependentFixtureInterface
{
    protected function loadData(ObjectManager $manager)
    {
        $this->createMany(Squadron::class, 10, function(Squadron $squadron, $count) {
            $squadron->setName($this->faker->words)
                ->setIdCode($this->faker->bothify('****'))
                ->setAdmin($this->getRandomReference(User::class))
                ->setDescription($this->faker->sentences($this->faker->numberBetween(2,5),true))
                ->setWelcomeMessage($this->faker->paragraphs($this->faker->numberBetween(2,5),true))
                ->setFaction($this->getRandomReferenceByORM(Faction::class, FactionRepository::class))
                ->setPower($this->getRandomReferenceByORM(Power::class, PowerRepository::class))
                ->setPlatform($this->getRandomReferenceByORM(Platform::class, PlatformRepository::class));
        });

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            User::class
        ];
    }


}
