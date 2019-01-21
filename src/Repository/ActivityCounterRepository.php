<?php

namespace App\Repository;

use App\Entity\ActivityCounter;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ActivityCounter|null find($id, $lockMode = null, $lockVersion = null)
 * @method ActivityCounter|null findOneBy(array $criteria, array $orderBy = null)
 * @method ActivityCounter[]    findAll()
 * @method ActivityCounter[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ActivityCounterRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ActivityCounter::class);
    }

    // /**
    //  * @return ActivityCounter[] Returns an array of ActivityCounter objects
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
    public function findOneBySomeField($value): ?ActivityCounter
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
