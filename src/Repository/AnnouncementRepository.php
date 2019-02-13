<?php

namespace App\Repository;

use App\Entity\Announcement;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Announcement|null find($id, $lockMode = null, $lockVersion = null)
 * @method Announcement|null findOneBy(array $criteria, array $orderBy = null)
 * @method Announcement[]    findAll()
 * @method Announcement[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AnnouncementRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Announcement::class);
    }

    public function findAllbyPublishStatus(?string $value) {
        $now = new \DateTime('now');

        $qb = $this->createQueryBuilder('a');
        return $qb->select('a.id as id', 'a.title as title', 'a.message as message', 'a.pinned_flag as pinned_flag', 'a.publish_at as publish_in', 'a.createdAt as created_in')
            ->addSelect('u.commander_name as author')
            ->join('a.user', 'u')
            ->andWhere('a.squadron = :val and a.publish_at < :now and a.published_flag=1')
            ->setParameter('val', $value)
            ->setParameter('now', $now)
            ->orderBy('a.pinned_flag', 'DESC')
            ->addOrderBy('a.publish_at', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function recordsTotal(?string $value = "")
    {
        $qb = $this->createQueryBuilder('q')
            ->select("count(q.id)");

        if($value != "") {
            $qb->andWhere('q.squadron = :val')
                ->setParameter('val', $value);
        }

        return $qb->getQuery()->getSingleScalarResult();
    }

    public function findAllBySquadron(?string $value, ?string $term): QueryBuilder
    {
        $qb = $this->createQueryBuilder('a')
            ->addSelect('u.commander_name')
            ->join('a.user', 'u')
            ->andWhere('a.squadron = :val')
            ->setParameter('val', $value);

        if($term) {
            $qb->andWhere('a.title like :term or u.commander_name like :term or a.message like :term')
                ->setParameter('term', '%' . $term . '%');
        }
        return $qb;
    }

    public function findAllBySquadronDatatables(?string $value, $params) {

        /**
         * @var QueryBuilder $qb
         */
        $qb = $this->createQueryBuilder('a');

        $qCount = $this->createQueryBuilder('a')
            ->select('COUNT(u.id) as num')
            ->join('a.user', 'u')
            ->where('a.squadron = :val')
            ->setParameter('val', $value);

        $qb->select('a.id as id','a.title as title', 'a.pinned_flag as pinned_flag', 'a.published_flag as published_flag', 'a.publish_at as publish_in', 'a.createdAt as created_in')
            ->addSelect('u.commander_name as author')
            ->join('a.user', 'u')
            ->andWhere('a.squadron = :val')
            ->setParameter('val', $value);

        foreach($params['order'] as $param) {
            $qb->addOrderBy($param['name'], $param['dir']);
        }

        if($params['search']['value']?: 0) {
            $qb->andWhere('a.title like :term or u.commander_name like :term or a.message like :term')
                ->setParameter('term', '%' . $params['search']['value'] . '%');
            $qCount->andWhere('a.title like :term or u.commander_name like :term or a.message like :term')
                ->setParameter('term', '%' . $params['search']['value'] . '%');
        }

        if(isset($params['start']) ?: 0) {
            $qb->setFirstResult($params['start']);
        }

        if(isset($params['length']) ?: 0) {
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
    //  * @return Announcement[] Returns an array of Announcement objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('a.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Announcement
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
