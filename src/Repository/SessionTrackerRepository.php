<?php

namespace App\Repository;

use App\Entity\SessionTracker;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method SessionTracker|null find($id, $lockMode = null, $lockVersion = null)
 * @method SessionTracker|null findOneBy(array $criteria, array $orderBy = null)
 * @method SessionTracker[]    findAll()
 * @method SessionTracker[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SessionTrackerRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, SessionTracker::class);
    }

    // /**
    //  * @return SessionTracker[] Returns an array of SessionTracker objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('s.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?SessionTracker
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
