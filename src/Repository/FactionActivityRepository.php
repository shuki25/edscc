<?php

namespace App\Repository;

use App\Entity\FactionActivity;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method FactionActivity|null find($id, $lockMode = null, $lockVersion = null)
 * @method FactionActivity|null findOneBy(array $criteria, array $orderBy = null)
 * @method FactionActivity[]    findAll()
 * @method FactionActivity[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FactionActivityRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, FactionActivity::class);
    }

    // /**
    //  * @return FactionActivity[] Returns an array of FactionActivity objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('f.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?FactionActivity
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
