<?php

namespace App\Repository;

use App\Entity\MinorFaction;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method MinorFaction|null find($id, $lockMode = null, $lockVersion = null)
 * @method MinorFaction|null findOneBy(array $criteria, array $orderBy = null)
 * @method MinorFaction[]    findAll()
 * @method MinorFaction[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MinorFactionRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, MinorFaction::class);
    }

    /**
     * @return MinorFaction[]
     */
    public function findThargoidFactions()
    {
        return $this->createQueryBuilder('mf')
            ->where('mf.name = ?1 or mf.name =?2')
            ->setParameter('1', 'Thargoids')
            ->setParameter('2', '$faction_Thargoid;')
            ->getQuery()->getResult();
    }

    // /**
    //  * @return MinorFaction[] Returns an array of MinorFaction objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('m.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?MinorFaction
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
