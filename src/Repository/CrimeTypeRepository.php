<?php

namespace App\Repository;

use App\Entity\CrimeType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method CrimeType|null find($id, $lockMode = null, $lockVersion = null)
 * @method CrimeType|null findOneBy(array $criteria, array $orderBy = null)
 * @method CrimeType[]    findAll()
 * @method CrimeType[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CrimeTypeRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, CrimeType::class);
    }

    // /**
    //  * @return CrimeType[] Returns an array of CrimeType objects
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
    public function findOneBySomeField($value): ?CrimeType
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
