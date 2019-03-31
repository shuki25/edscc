<?php

namespace App\Repository;

use App\Entity\CustomFilter;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method CustomFilter|null find($id, $lockMode = null, $lockVersion = null)
 * @method CustomFilter|null findOneBy(array $criteria, array $orderBy = null)
 * @method CustomFilter[]    findAll()
 * @method CustomFilter[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CustomFilterRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, CustomFilter::class);
    }

    // /**
    //  * @return CustomFilter[] Returns an array of CustomFilter objects
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
    public function findOneBySomeField($value): ?CustomFilter
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
