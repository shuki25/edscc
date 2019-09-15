<?php

namespace App\Repository;

use App\Entity\AchievementRule;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method AchievementRule|null find($id, $lockMode = null, $lockVersion = null)
 * @method AchievementRule|null findOneBy(array $criteria, array $orderBy = null)
 * @method AchievementRule[]    findAll()
 * @method AchievementRule[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AchievementRuleRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, AchievementRule::class);
    }

    /**
     * @return AchievementRule[] Returns an array of AchievementRule objects
     */
    public function findLockedAchievements($user)
    {
        $expr = $this->getEntityManager()->getExpressionBuilder();
        $qb = $this->createQueryBuilder('ar')
            ->where(
                $expr->notIn(
                    'ar.id',
                    $this->getEntityManager()->createQueryBuilder()
                        ->select('IDENTITY(a.achievement_rule)')
                        ->from('App\Entity\Achievement', 'a')
                        ->where('a.user = :user')
                        ->getDQL()
                )
            )
            ->setParameter('user', $user);
        $query = $qb->getQuery();
        return $query->getResult();
    }

    // /**
    //  * @return AchievementRule[] Returns an array of AchievementRule objects
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
    public function findOneBySomeField($value): ?AchievementRule
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
