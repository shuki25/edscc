<?php

namespace App\DataFixtures;

use App\Entity\Squadron;
use Doctrine\Common\Persistence\ObjectManager;

class SquadronFixtures extends BaseFixtures
{
    public function loadData(ObjectManager $manager)
    {
        $this->createMany(User::class, 200, function(Squadron $squadron, $count) {
            $squadron->setName()
                ->setDescription()
                ->setWelcomeMessage()
                ->setFaction()
                ->setPower()
                ->setPlatform();
        });

        $manager->flush();
    }
}
