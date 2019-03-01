<?php

namespace App\Repository;

use App\Entity\ImportQueue;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ImportQueue|null find($id, $lockMode = null, $lockVersion = null)
 * @method ImportQueue|null findOneBy(array $criteria, array $orderBy = null)
 * @method ImportQueue[]    findAll()
 * @method ImportQueue[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ImportQueueRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ImportQueue::class);
    }

    public function recordsTotal(?string $value = "")
    {
        $qb = $this->createQueryBuilder('q')
            ->select("count(q.id)");

        if ($value != "") {
            $qb->andWhere('q.user = :val')
                ->setParameter('val', $value);
        }

        return $qb->getQuery()->getSingleScalarResult();
    }

    public function totalCountInQueue()
    {
        $qb = $this->createQueryBuilder('q')
            ->select("count(q.id)")
            ->andWhere("q.progress_code = :val")
            ->setParameter('val', "Q");

        return $qb->getQuery()->getSingleScalarResult();
    }

    public function findAllByUserDatatables(?string $value, $params)
    {

        /**
         * @var QueryBuilder $qb
         */
        $qb = $this->createQueryBuilder('iq');

        $qCount = $this->createQueryBuilder('iq')
            ->select('COUNT(iq.id) as num')
            ->where('iq.user = :val')
            ->setParameter('val', $value);

        $qb->select('iq.id as id', 'iq.original_filename as original_filename', 'iq.game_datetime as game_datetime', 'iq.progress_code as progress_code', 'iq.time_started as time_started, iq.progress_percent as progress_percent')
            ->andWhere('iq.user = :val')
            ->setParameter('val', $value);

        foreach ($params['order'] as $param) {
            $qb->addOrderBy($param['name'], $param['dir']);
        }

        if (isset($params['start']) ?: 0) {
            $qb->setFirstResult($params['start']);
        }

        if (isset($params['length']) ?: 0) {
            $qb->setMaxResults($params['length']);
        }

        $data = [
            'data' => $qb->getQuery()->getArrayResult(),
            'recordsFiltered' => $qCount->getQuery()->getSingleScalarResult(),
            'recordsTotal' => $this->recordsTotal($value)
        ];

        return $data;
    }

    // /**
    //  * @return ImportQueue[] Returns an array of ImportQueue objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('i.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?ImportQueue
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
