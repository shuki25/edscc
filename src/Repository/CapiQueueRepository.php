<?php

namespace App\Repository;

use App\Entity\CapiQueue;
use App\Entity\User;
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

    public function totalCountInQueue($code = "Q")
    {
        $qb = $this->createQueryBuilder('q')
            ->select("count(q.id)")
            ->andWhere("q.progress_code = :val")
            ->setParameter('val', $code);

        return $qb->getQuery()->getSingleScalarResult();
    }

    public function countInQueueByUser(User $user)
    {
        $qb = $this->createQueryBuilder('q')
            ->select("count(q.id)")
            ->andWhere("q.progress_code in ('Q', 'R')")
            ->andWhere("q.user = :user")
            ->setParameter('user', $user);

        return $qb->getQuery()->getSingleScalarResult();
    }

    public function nextInRetryQueue($progress_code = "Q", $timeout = 5): ?CapiQueue
    {
//        $utc = new \DateTimeZone('UTC');
        $timeout_dt = new \DateTime('now');
        $interval = sprintf("PT%dM", $timeout);
        $timeout_dt->sub(new \DateInterval($interval));

        return $this->createQueryBuilder('q')
            ->andWhere('q.progress_code = :val1')
            ->andWhere('q.updatedAt < :val2')
            ->setParameter('val1', $progress_code)
            ->setParameter('val2', $timeout_dt)
            ->orderBy('q.journal_date', 'ASC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
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
