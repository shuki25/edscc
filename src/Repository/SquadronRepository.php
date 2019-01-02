<?php

namespace App\Repository;

use App\Entity\Squadron;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Squadron|null find($id, $lockMode = null, $lockVersion = null)
 * @method Squadron|null findOneBy(array $criteria, array $orderBy = null)
 * @method Squadron[]    findAll()
 * @method Squadron[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SquadronRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Squadron::class);
    }

    // /**
    //  * @return Squadron[] Returns an array of Squadron objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('s.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Squadron
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
