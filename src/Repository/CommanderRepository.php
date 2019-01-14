<?php

namespace App\Repository;

use App\Entity\Commander;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Commander|null find($id, $lockMode = null, $lockVersion = null)
 * @method Commander|null findOneBy(array $criteria, array $orderBy = null)
 * @method Commander[]    findAll()
 * @method Commander[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CommanderRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Commander::class);
    }

    // /**
    //  * @return Commander[] Returns an array of Commander objects
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
    public function findOneBySomeField($value): ?Commander
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
