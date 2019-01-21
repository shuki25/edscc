<?php

namespace App\Repository;

use App\Entity\EarningHistory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method EarningHistory|null find($id, $lockMode = null, $lockVersion = null)
 * @method EarningHistory|null findOneBy(array $criteria, array $orderBy = null)
 * @method EarningHistory[]    findAll()
 * @method EarningHistory[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EarningHistoryRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, EarningHistory::class);
    }

    // /**
    //  * @return EarningHistory[] Returns an array of EarningHistory objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('e.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?EarningHistory
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
