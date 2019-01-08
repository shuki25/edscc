<?php

namespace App\Repository;

use App\Entity\Faction;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Faction|null find($id, $lockMode = null, $lockVersion = null)
 * @method Faction|null findOneBy(array $criteria, array $orderBy = null)
 * @method Faction[]    findAll()
 * @method Faction[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FactionRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Faction::class);
    }

    // /**
    //  * @return Faction[] Returns an array of Faction objects
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
    public function findOneBySomeField($value): ?Faction
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
