<?php

namespace App\Repository;

use App\Entity\Review;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Review>
 */
class ReviewRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Review::class);
    }

    // Security: All queries use Doctrine's QueryBuilder and parameter binding, ensuring no risk of SQL injection.
    //    /**
    //     * @return Review[] Returns an array of Review objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('r')
    //            ->andWhere('r.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('r.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Review
    //    {
    //        return $this->createQueryBuilder('r')
    //            ->andWhere('r.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }

    /**
     * Find reviews by user, with optional session and rating filters.
     *
     * @param User $user
     * @param Session|null $session
     * @param int|null $rating
     * @return Review[]
     */
    public function findByUserWithFilters($user, $session = null, $rating = null): array
    {
        $qb = $this->createQueryBuilder('r')
            ->andWhere('r.user = :user')
            ->setParameter('user', $user)
            ->orderBy('r.created_at', 'DESC');

        if ($session) {
            $qb->andWhere('r.session = :session')
               ->setParameter('session', $session);
        }
        if ($rating) {
            $qb->andWhere('r.rating = :rating')
               ->setParameter('rating', $rating);
        }
        return $qb->getQuery()->getResult();
    }
}
