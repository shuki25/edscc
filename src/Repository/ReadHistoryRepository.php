<?php

namespace App\Repository;

use App\Entity\ReadHistory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ReadHistory|null find($id, $lockMode = null, $lockVersion = null)
 * @method ReadHistory|null findOneBy(array $criteria, array $orderBy = null)
 * @method ReadHistory[]    findAll()
 * @method ReadHistory[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ReadHistoryRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ReadHistory::class);
    }

    // /**
    //  * @return ReadHistory[] Returns an array of ReadHistory objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('r.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?ReadHistory
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
