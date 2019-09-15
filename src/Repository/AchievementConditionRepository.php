<?php

namespace App\Repository;

use App\Entity\AchievementCondition;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method AchievementCondition|null find($id, $lockMode = null, $lockVersion = null)
 * @method AchievementCondition|null findOneBy(array $criteria, array $orderBy = null)
 * @method AchievementCondition[]    findAll()
 * @method AchievementCondition[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AchievementConditionRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, AchievementCondition::class);
    }

    // /**
    //  * @return AchievementCondition[] Returns an array of AchievementCondition objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('a.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?AchievementCondition
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
