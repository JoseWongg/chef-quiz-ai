<?php

namespace App\Repository;

use App\Entity\FoodSafetyBestPractices;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<FoodSafetyBestPractices>
 *
 * @method FoodSafetyBestPractices|null find($id, $lockMode = null, $lockVersion = null)
 * @method FoodSafetyBestPractices|null findOneBy(array $criteria, array $orderBy = null)
 * @method FoodSafetyBestPractices[]    findAll()
 * @method FoodSafetyBestPractices[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FoodSafetyBestPracticesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FoodSafetyBestPractices::class);
    }

        // Returns a random best practice for a given topic

    ///**
     //* @throws NonUniqueResultException
     //*/
    //public function findRandomRuleByTopic(string $topic): ?FoodSafetyBestPractices
        //{
            //return $this->createQueryBuilder('f')
                //->andWhere('f.topic = :topic')
                //->setParameter('topic', $topic)
                //->orderBy('RAND() as HIDDEN rand') // MySQL specific. For other databases we need to use a different method to order randomly
                //->setMaxResults(1)
                //->getQuery()
                //->getOneOrNullResult();
        //}


    /**
     * @throws Exception
     */
    public function findRandomRuleByTopic(string $topic): ?FoodSafetyBestPractices
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = '
        SELECT * FROM food_safety_best_practices
        WHERE topic = :topic
        ORDER BY RAND()
        LIMIT 1
    ';

        // Execute the query and get the Statement object
        $stmt = $conn->executeQuery($sql, ['topic' => $topic]);

        // Fetch the result as an associative array
        $ruleData = $stmt->fetchAssociative();

        if (!$ruleData) {
            return null;
        }

        // Retrieve the entity using the found ID
        return $this->find($ruleData['id']);
    }
























//    /**
//     * @return FoodSafetyBestPractices[] Returns an array of FoodSafetyBestPractices objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('f')
//            ->andWhere('f.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('f.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?FoodSafetyBestPractices
//    {
//        return $this->createQueryBuilder('f')
//            ->andWhere('f.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
