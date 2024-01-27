<?php

namespace App\Repository;

use App\Entity\AssignedQuiz;
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
