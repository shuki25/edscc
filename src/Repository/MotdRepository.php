<?php

namespace App\Repository;

use App\Entity\Motd;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Motd|null find($id, $lockMode = null, $lockVersion = null)
 * @method Motd|null findOneBy(array $criteria, array $orderBy = null)
 * @method Motd[]    findAll()
 * @method Motd[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MotdRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Motd::class);
    }

    public function recordsTotal(?string $value = "")
    {
        $qb = $this->createQueryBuilder('q')
            ->select("count(q.id)");

        if ($value != "") {
            $qb->andWhere('q.squadron = :val')
                ->setParameter('val', $value);
        }

        return $qb->getQuery()->getSingleScalarResult();
    }

    public function findAllByDatatables($params)
    {

        /**
         * @var QueryBuilder $qb
         */
        $qb = $this->createQueryBuilder('m');

        $qCount = $this->createQueryBuilder('m')
            ->select('COUNT(m.id) as num');

        $qb->select('m.id as id', 'm.title as title', 'm.message as message', 'm.show_flag as show_flag', 'm.show_login as show_login', 'm.createdAt as created_in');

        foreach ($params['order'] as $param) {
            $qb->addOrderBy($param['name'], $param['dir']);
        }

        if ($params['search']['value'] ?: 0) {
            $qb->andWhere('m.title like :term or m.message like :term')
                ->setParameter('term', '%' . $params['search']['value'] . '%');
            $qCount->andWhere('m.title like :term or m.message like :term')
                ->setParameter('term', '%' . $params['search']['value'] . '%');
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
            'recordsTotal' => $this->recordsTotal()
        ];

        return $data;
    }

    // /**
    //  * @return Motd[] Returns an array of Motd objects
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
    public function findOneBySomeField($value): ?Motd
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
