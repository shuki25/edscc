<?php

namespace App\Repository;

use App\Entity\CapiQueue;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method CapiQueue|null find($id, $lockMode = null, $lockVersion = null)
 * @method CapiQueue|null findOneBy(array $criteria, array $orderBy = null)
 * @method CapiQueue[]    findAll()
 * @method CapiQueue[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CapiQueueRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, CapiQueue::class);
    }

    public function totalCountInQueue()
    {
        $qb = $this->createQueryBuilder('q')
            ->select("count(q.id)")
            ->andWhere("q.progress_code = :val")
            ->setParameter('val', "Q");

        return $qb->getQuery()->getSingleScalarResult();
    }

    // /**
    //  * @return CapiQueue[] Returns an array of CapiQueue objects
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
    public function findOneBySomeField($value): ?CapiQueue
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
