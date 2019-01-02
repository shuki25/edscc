<?php

namespace App\Repository;

use App\Entity\VerifyToken;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method VerifyToken|null find($id, $lockMode = null, $lockVersion = null)
 * @method VerifyToken|null findOneBy(array $criteria, array $orderBy = null)
 * @method VerifyToken[]    findAll()
 * @method VerifyToken[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VerifyTokenRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, VerifyToken::class);
    }

    // /**
    //  * @return VerifyToken[] Returns an array of VerifyToken objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('v')
            ->andWhere('v.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('v.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?VerifyToken
    {
        return $this->createQueryBuilder('v')
            ->andWhere('v.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
