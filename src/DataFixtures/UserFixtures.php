<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Common\Persistence\ObjectManager;

class UserFixtures extends BaseFixtures
{
    protected function loadData(ObjectManager $manager)
    {
        $this->createMany(User::class, 200, function(User $user, $count) {
            $user->setEmail()
                ->setCommanderName()
                ->setPassword('test123');
        });

        $mabeastnager->flush();
    }
}

