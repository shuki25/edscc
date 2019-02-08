<?php

namespace App\Repository;

use App\Entity\SquadronTags;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method SquadronTags|null find($id, $lockMode = null, $lockVersion = null)
 * @method SquadronTags|null findOneBy(array $criteria, array $orderBy = null)
 * @method SquadronTags[]    findAll()
 * @method SquadronTags[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SquadronTagsRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, SquadronTags::class);
    }

    // /**
    //  * @return SquadronTags[] Returns an array of SquadronTags objects
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
    public function findOneBySomeField($value): ?SquadronTags
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
