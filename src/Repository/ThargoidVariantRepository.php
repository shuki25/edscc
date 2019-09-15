<?php

namespace App\Repository;

use App\Entity\ThargoidVariant;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method ThargoidVariant|null find($id, $lockMode = null, $lockVersion = null)
 * @method ThargoidVariant|null findOneBy(array $criteria, array $orderBy = null)
 * @method ThargoidVariant[]    findAll()
 * @method ThargoidVariant[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ThargoidVariantRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ThargoidVariant::class);
    }

    // /**
    //  * @return ThargoidVariant[] Returns an array of ThargoidVariant objects
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
    public function findOneBySomeField($value): ?ThargoidVariant
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
