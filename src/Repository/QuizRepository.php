<?php

namespace App\Repository;

use App\Entity\Quiz;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\QueryBuilder;

/**
 * @extends ServiceEntityRepository<Quiz>
 *
 * @method Quiz|null find($id, $lockMode = null, $lockVersion = null)
 * @method Quiz|null findOneBy(array $criteria, array $orderBy = null)
 * @method Quiz[]    findAll()
 * @method Quiz[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class QuizRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Quiz::class);
    }






    /**
     * Finds quizzes based on filter criteria.
     *
     * @param \DateTimeInterface|null $dateFrom
     * @param \DateTimeInterface|null $dateTo
     * @param string|null $topic
     * @param string|null $trainerName
     * @return Quiz[]
     */
    public function findByFilters2(?\DateTimeInterface $dateFrom, ?\DateTimeInterface $dateTo, ?string $topic, ?string $trainerName): array
    {
        $queryBuilder = $this->createQueryBuilder('q')
            ->leftJoin('q.trainer', 't');

        if (!empty($topic)) {
            $queryBuilder->andWhere('q.topic LIKE :topic')
                ->setParameter('topic', '%' . $topic . '%');
        }

        if (!empty($trainerName)) {
            $queryBuilder->andWhere('t.name = :trainerName')
                ->setParameter('trainerName', $trainerName);
        }

        // Add date filters if both dateFrom and dateTo are provided
        if ($dateFrom && $dateTo) {
            $queryBuilder->andWhere('q.creationDate BETWEEN :dateFrom AND :dateTo')
                ->setParameter('dateFrom', $dateFrom)
                ->setParameter('dateTo', $dateTo);
        }

        return $queryBuilder->getQuery()->getResult();
    }


/**

    // original version of the method
    public function findByFilters($trainerName, $topic)
    {
        $qb = $this->createQueryBuilder('q')
            ->leftJoin('q.trainer', 't');

        if ($trainerName) {
            $qb->andWhere('t.name = :trainerName')
                ->setParameter('trainerName', $trainerName);
        }

        if ($topic) {
            $qb->andWhere('q.type = :topic')
                ->setParameter('topic', $topic);
        }

        return $qb->getQuery()->getResult();
    }
**/




    public function findByFiltersQueryBuilder($trainerName, $topic): QueryBuilder {
        $qb = $this->createQueryBuilder('q')
            ->leftJoin('q.trainer', 't');

        if ($trainerName) {
            $qb->andWhere('t.name = :trainerName')
                ->setParameter('trainerName', $trainerName);
        }

        if ($topic) {
            $qb->andWhere('q.type = :topic')
                ->setParameter('topic', $topic);
        }

        // Return the query builder instead of executing the query
        return $qb;
    }














//    /**
//     * @return Quiz[] Returns an array of Quiz objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('q')
//            ->andWhere('q.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('q.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Quiz
//    {
//        return $this->createQueryBuilder('q')
//            ->andWhere('q.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
