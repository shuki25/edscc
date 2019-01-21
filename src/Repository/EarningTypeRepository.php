<?php

namespace App\Repository;

use App\Entity\EarningType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method EarningType|null find($id, $lockMode = null, $lockVersion = null)
 * @method EarningType|null findOneBy(array $criteria, array $orderBy = null)
 * @method EarningType[]    findAll()
 * @method EarningType[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EarningTypeRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, EarningType::class);
    }

    // /**
    //  * @return EarningType[] Returns an array of EarningType objects
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
    public function findOneBySomeField($value): ?EarningType
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
