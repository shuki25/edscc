<?php

namespace App\Repository;

use App\Entity\CustomRank;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method CustomRank|null find($id, $lockMode = null, $lockVersion = null)
 * @method CustomRank|null findOneBy(array $criteria, array $orderBy = null)
 * @method CustomRank[]    findAll()
 * @method CustomRank[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CustomRankRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, CustomRank::class);
    }

    // /**
    //  * @return CustomRank[] Returns an array of CustomRank objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?CustomRank
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
