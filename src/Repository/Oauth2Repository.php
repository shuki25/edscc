<?php

namespace App\Repository;

use App\Entity\Oauth2;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Oauth2|null find($id, $lockMode = null, $lockVersion = null)
 * @method Oauth2|null findOneBy(array $criteria, array $orderBy = null)
 * @method Oauth2[]    findAll()
 * @method Oauth2[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class Oauth2Repository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Oauth2::class);
    }

    // /**
    //  * @return Oauth2[] Returns an array of Oauth2 objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('o.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Oauth2
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
