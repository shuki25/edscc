<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function recordsTotal(?string $value = "")
    {
        $qb = $this->createQueryBuilder('q')
            ->select("count(q.id)");

        if($value != "") {
            $qb->andWhere('q.Squadron = :val')
                ->setParameter('val', $value);
        }

        return $qb->getQuery()->getSingleScalarResult();
    }

    /**
    * @return User[] Returns an array of User objects
    */
    public function findValidTokens($value)
    {
        return $this->createQueryBuilder('u')
            ->join('u.verifyTokens', 'vt')
            ->andWhere('u.id = ?1 and vt.expiresAt > ?2')
            ->setParameter(1, $value)
            ->setParameter(2,date('Y-m-d H:i:s'))
            ->orderBy('vt.expiresAt', 'DESC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }

    public function findAllBySquadron(?string $value, ?string $term): QueryBuilder
    {
        /**
         * @var QueryBuilder $qb
         */
        $qb = $this->createQueryBuilder('u')
            ->addSelect('s.name, r.name')
            ->join('u.status', 's')
            ->join('u.rank', 'r')
            ->andWhere('u.Squadron = :val')
            ->setParameter('val', $value);

        if($term) {
            $qb->andWhere('u.commander_name like :term or s.name like :term or r.name like :term')
                ->setParameter('term', '%' . $term . '%');
        }
        return $qb;
    }

    public function findAllBySquadronDatatables(?string $value, $params) {

        /**
         * @var QueryBuilder $qb
         */
        $qb = $this->createQueryBuilder('u');

        $qCount = $this->createQueryBuilder('u')
            ->select('COUNT(u.id) as num')
            ->join('u.status', 's')
            ->join('u.custom_rank', 'cr')
            ->where('u.Squadron = :val')
            ->setParameter('val', $value);


        $qb->select('u.id as id','u.commander_name as commander_name','u.createdAt as join_date', 'u.LastLoginAt as last_login_at')
            ->addSelect('s.name as status, cr.name as rank, s.tag as tag')
            ->join('u.status', 's')
            ->join('u.custom_rank', 'cr')
            ->andWhere('u.Squadron = :val')
            ->setParameter('val', $value);

        foreach($params['order'] as $param) {
            $qb->addOrderBy($param['name'], $param['dir']);
        }

        if($params['search']['value']?: 0) {
            $qb->andWhere('u.commander_name like :term or s.name like :term or cr.name like :term')
                ->setParameter('term', '%' . $params['search']['value'] . '%');
            $qCount->andWhere('u.commander_name like :term or s.name like :term or cr.name like :term')
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
    //  * @return User[] Returns an array of User objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('u.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?User
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
