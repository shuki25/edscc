<?php

namespace App\Repository;

use App\Entity\Edmc;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Edmc|null find($id, $lockMode = null, $lockVersion = null)
 * @method Edmc|null findOneBy(array $criteria, array $orderBy = null)
 * @method Edmc[]    findAll()
 * @method Edmc[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EdmcRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Edmc::class);
    }

    // /**
    //  * @return Edmc[] Returns an array of Edmc objects
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
    public function findOneBySomeField($value): ?Edmc
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
