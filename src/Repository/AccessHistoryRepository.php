<?php

namespace App\Repository;

use App\Entity\AccessHistory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method AccessHistory|null find($id, $lockMode = null, $lockVersion = null)
 * @method AccessHistory|null findOneBy(array $criteria, array $orderBy = null)
 * @method AccessHistory[]    findAll()
 * @method AccessHistory[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AccessHistoryRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, AccessHistory::class);
    }

    // /**
    //  * @return AccessHistory[] Returns an array of AccessHistory objects
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
    public function findOneBySomeField($value): ?AccessHistory
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
