<?php

namespace App\Repository;

use App\Entity\AssignedQuiz;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<AssignedQuiz>
 *
 * @method AssignedQuiz|null find($id, $lockMode = null, $lockVersion = null)
 * @method AssignedQuiz|null findOneBy(array $criteria, array $orderBy = null)
 * @method AssignedQuiz[]    findAll()
 * @method AssignedQuiz[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AssignedQuizRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AssignedQuiz::class);
    }


    public function findByFilters(User $user, ?string $completionFilter, ?string $topicFilter): array
    {

        $qb = $this->createQueryBuilder('aq')
            ->leftJoin('aq.quiz', 'q')
            ->where('aq.chef = :user')
            ->setParameter('user', $user);

        // Filter by completion status
        if ($completionFilter === 'completed') {
            $qb->andWhere('aq.completed = true');
        } elseif ($completionFilter === 'incomplete') {
            $qb->andWhere('aq.completed = false');
        }

        // Filter by topic
        if ($topicFilter) {
            $qb->andWhere('q.type = :topic')
                ->setParameter('topic', $topicFilter);
        }

        return $qb->getQuery()->getResult();
    }












//    /**
//     * @return AssignedQuiz[] Returns an array of AssignedQuiz objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('a.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?AssignedQuiz
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
