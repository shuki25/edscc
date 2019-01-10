<?php
/**
 * Created by PhpStorm.
 * User: josh
 * Date: 2019-01-10
 * Time: 15:29
 */

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

abstract class BaseFixtures extends Fixture
{
    /** @var ObjectManager */
    private $manager;

    abstract protected function loadData(ObjectManager $em);

    public function load(ObjectManager $manager)
    {
        $this->manager = $manager;
        $this->loadData($manager);
    }

    protected function createMany(string $className, int $count, callable $factory)
    {
        for($i=0; $i < $count; $i++) {
            $entity = new $className();
            $factory($entity, $i);

            $this->manager->persist($entity);
            $this->addReference($className . '_' . $i, $entity);
        }
    }
}