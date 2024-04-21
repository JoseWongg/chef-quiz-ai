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



    // Find quizzes based on filter criteria
    public function findByFiltersQueryBuilder($trainerName, $topic, $userId = null): QueryBuilder {
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

        if ($userId) {
            // Create a subquery to select quizzes that have been assigned to the specified user
            $subQuery = $this->_em->createQueryBuilder()
                ->select('IDENTITY(aq.quiz)')
                ->from('App\Entity\AssignedQuiz', 'aq')
                ->where('aq.chef = :userId')
                ->getDQL();

            $qb->andWhere($qb->expr()->in('q.id', $subQuery))
                ->setParameter('userId', $userId);
        }

        return $qb;
    }

/**
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
    **/
}