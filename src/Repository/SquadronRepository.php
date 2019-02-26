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

    /**
     * @return Squadron[] Returns an array of Squadron objects
     */
    public function findValidTokens($value)
    {
        return $this->createQueryBuilder('u')
            ->join('u.verifyTokens', 'vt')
            ->andWhere('u.id = ?1 and vt.expiresAt > ?2')
            ->setParameter(1, $value)
            ->setParameter(2, date('Y-m-d H:i:s'))
            ->orderBy('vt.expiresAt', 'DESC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Squadron[]
     */
    public function findAllActiveSquadrons()
    {
        return $this->createQueryBuilder('s')
            ->where('s.id > 1')
            ->orderBy('s.name')
            ->getQuery()
            ->getResult();
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
