<?php

namespace App\DataFixtures;

use App\Entity\Announcement;
use App\Entity\Squadron;
use App\Entity\User;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class AnnouncementFixture extends BaseFixture implements DependentFixtureInterface
{
    public function loadData(ObjectManager $manager)
    {
        $this->createMany(Announcement::class, 100, function(Announcement $announcement, $count) {
           $announcement->setTitle($this->faker->sentence(10))
               ->setMessage($this->faker->paragraphs($this->faker->numberBetween(2,5),true))
               ->setSquadron($this->getRandomReference(Squadron::class))
               ->setUser($this->getRandomReference(User::class));
        });

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            User::class,
            Squadron::class
        ];
    }


}
