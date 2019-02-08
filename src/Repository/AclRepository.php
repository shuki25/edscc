<?php

namespace App\Repository;

use App\Entity\Acl;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Acl|null find($id, $lockMode = null, $lockVersion = null)
 * @method Acl|null findOneBy(array $criteria, array $orderBy = null)
 * @method Acl[]    findAll()
 * @method Acl[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AclRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Acl::class);
    }

    // /**
    //  * @return Acl[] Returns an array of Acl objects
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
    public function findOneBySomeField($value): ?Acl
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
