<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Common\Persistence\ObjectManager;

class UserFixture extends BaseFixture
{
    protected function loadData(ObjectManager $manager)
    {
        $this->createMany(User::class, 200, function(User $user, $count) {
            $user->setEmail($this->faker->email)
                ->setCommanderName($this->faker->userName)
                ->setPassword('test123')
                ->setAvatarUrl($this->faker->imageUrl(200,200));
        });

        $manager->flush();
    }
}

