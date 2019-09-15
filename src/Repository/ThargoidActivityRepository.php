<?php

namespace App\Repository;

use App\Entity\ThargoidActivity;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method ThargoidActivity|null find($id, $lockMode = null, $lockVersion = null)
 * @method ThargoidActivity|null findOneBy(array $criteria, array $orderBy = null)
 * @method ThargoidActivity[]    findAll()
 * @method ThargoidActivity[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ThargoidActivityRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ThargoidActivity::class);
    }

    // /**
    //  * @return ThargoidActivity[] Returns an array of ThargoidActivity objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('t.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?ThargoidActivity
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
