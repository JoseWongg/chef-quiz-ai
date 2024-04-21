<?php

namespace App\Controller;

use App\Entity\AssignedQuiz;
use App\Entity\Quiz;
use App\Entity\User;
use App\Repository\AssignedQuizRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\UserRepository;
use App\Repository\QuizRepository;
use App\Services\AssignedQuizPreviewFormattingService;
use Knp\Component\Pager\PaginatorInterface;


class QuizRepositoryController extends AbstractController
{


    // Method to display the list of quizzes
    #[Route('/quiz/repository', name: 'app_quiz_repository')]
    public function listQuizzes(EntityManagerInterface $entityManager): Response
    {

        $topics = [
            "Cross-contamination",
            "Cooking Food",
            "Chilling Food",
            "Allergens",
            "Cleaning",
            "Initial Food Safety Training"
        ];


        $quizRepository = $entityManager->getRepository(Quiz::class);
        $quizzes = $quizRepository->findAll();

        // Fetch users from the database
        $userRepository = $entityManager->getRepository(User::class);
        $users = $userRepository->findAll();


        return $this->render('quiz_repository/index.html.twig', [
            'quizzes' => $quizzes,
            'users' => $users,
            'topics' => $topics
        ]);
    }


    // Method to display the details of a quiz
    #[Route('/quiz/details/{id}', name: 'app_quiz_details')]
    public function quizDetails(int $id, EntityManagerInterface $entityManager): Response
    {
        $quiz = $entityManager->getRepository(Quiz::class)->find($id);

        if (!$quiz) {
            throw $this->createNotFoundException('The quiz does not exist');
        }

        return $this->render('quiz_repository/quiz_details.html.twig', [
            'quiz' => $quiz,
        ]);
    }


    /**
     * @throws \Exception
     * Method to assign a quiz to a user
     */
    #[Route('/assign-quiz', name: 'assign_quiz', methods: ['POST'])]
    public function assignQuiz(Request $request, EntityManagerInterface $entityManager, UserRepository $userRepository, QuizRepository $quizRepository): Response
    {
        $data = json_decode($request->getContent(), true);
        $quizId = $data['quizId'];
        $userIds = $data['userIds'];
        $deadline = new \DateTime($data['deadline']);
        // Extract passingScore and ensure it's an integer and it is set
        $passingScore = isset($data['passingScore']) ? (int) $data['passingScore'] : null;
        // Validate passingScore
        if (null === $passingScore || $passingScore <= 0) {
            return new JsonResponse(['error' => 'Invalid passing score.']);
        }

        $quiz = $quizRepository->find($quizId);
        if (!$quiz) {
            return new JsonResponse(['error' => 'Quiz not found.']);
        }

        $assigner = $this->getUser();

        $alreadyAssignedOrPassed = [];

        foreach ($userIds as $userId) {
            $user = $userRepository->find($userId);

            // Fetch all assignments for the user and the specific quiz
            $previousAssignments = $entityManager->getRepository(AssignedQuiz::class)->findBy([
                'quiz' => $quiz,
                'chef' => $user
            ]);
            $canAssign = true;
            foreach ($previousAssignments as $assignment) {
                // Check if there's any completed assignment that the user passed or an active assignment
                if (!$assignment->isCompleted() || ($assignment->getMark() >= $assignment->getPassingScore())) {

                    // Found an assignment where the chef passed the quiz, cannot reassign
                    $canAssign = false;
                    $alreadyAssignedOrPassed[] = $user->getName();
                    break;
                }
            }
            if ($canAssign) {
                // Proceed to create and persist a new AssignedQuiz entity only if the user has never passed the quiz
                $assignedQuiz = new AssignedQuiz();
                $assignedQuiz->setQuiz($quiz)
                    ->setAssigner($assigner)
                    ->setChef($user)
                    ->setGeneratedDate(new \DateTime())
                    ->setDeadline($deadline)
                    ->setPassingScore($passingScore)
                    ->setMark(0)
                    ->setCompleted(false)
                    ->setProgression(0)
                    ->setResponses([]);

                $entityManager->persist($assignedQuiz);
            }
        }
        $entityManager->flush();

        // Alert for users who have already passed the quiz or have an active assignment
        if (!empty($alreadyAssignedOrPassed)) {

            $message = 'Quiz cannot be assigned to the following user(s) because they have already passed the quiz or have an active assignment: ' ."\n". "\n"."-".implode("\n-", $alreadyAssignedOrPassed);

            return new JsonResponse(['error' => $message], 400);
        }
        return new JsonResponse(['success' => 'Quiz assigned successfully.']);
    }


    #[Route('/quiz/history/{id}', name: 'app_quiz_history')]
    public function quizHistory(int $id, Request $request, AssignedQuizRepository $assignedQuizRepository, EntityManagerInterface $entityManager, PaginatorInterface $paginator): Response
    {
        $userRepository = $entityManager->getRepository(User::class);
        $users = $userRepository->findAll();

        $userId = $request->query->get('user');
        $page = $request->query->getInt('page', 1); // Default to the first page if not specified
        // Number of items per page
        $limit = 10;

        $criteria = ['quiz' => $id];
        if (!empty($userId)) {
            $criteria['chef'] = $userId;
        }

        $queryBuilder = $assignedQuizRepository->createQueryBuilder('a')
            ->where('a.quiz = :quiz')
            ->setParameter('quiz', $id);

        if ($userId) {
            $queryBuilder->andWhere('a.chef = :chef')
                ->setParameter('chef', $userId);
        }

        $queryBuilder->orderBy('a.generatedDate', 'ASC');
        $assignments = $paginator->paginate($queryBuilder, $page, $limit);

        return $this->render('quiz_repository/quiz_history.html.twig', [
            'assignments' => $assignments,
            'users' => $users,
            'currentPage' => $page,
            'totalPages' => ceil($assignments->getTotalItemCount() / $limit),
            'id' => $id,
        ]);
    }





















    // Method to view the details of an assigned quiz
    #[Route('/assigned-quiz/details/{id}', name: 'assigned_quiz_details')]
    public function assignedQuizDetails($id, AssignedQuizRepository $assignedQuizRepository, AssignedQuizPreviewFormattingService $assignedQuizPreviewFormattingService): Response
    {
        $assignedQuiz = $assignedQuizRepository->find($id);
        if (!$assignedQuiz) {
            throw $this->createNotFoundException('The assigned quiz does not exist.');
        }

        $formattedDetails = $assignedQuizPreviewFormattingService->formatAssignedQuizDetails($assignedQuiz);

        return $this->render('quiz_repository/assigned_quiz_details.html.twig', [
            'assignedQuiz' => $assignedQuiz,
            'formattedDetails' => $formattedDetails,
        ]);
    }


/**

    // original version of the method
    #[Route('/fetch-filtered-quizzes', name: 'fetch_filtered_quizzes')]
    public function fetchFilteredQuizzes(Request $request, QuizRepository $QuizRepository): JsonResponse
    {
        $trainerName = $request->query->get('trainer');
        $topic = $request->query->get('topic');
        $quizzes = $QuizRepository->findByFilters($trainerName, $topic);
        $quizzesArray = [];
        foreach ($quizzes as $quiz) {

            $quizzesArray[] = [
                'id' => $quiz->getId(),
                'creationDate' => $quiz->getCreationDate()->format('d-m-Y'),
                'type' => $quiz->getType(),
                'trainer' => [
                    'name' => $quiz->getTrainer()->getName(),
                ],
                'detailsUrl' => $this->generateUrl('app_quiz_details', ['id' => $quiz->getId()]),
                'historyUrl' => $this->generateUrl('app_quiz_history', ['id' => $quiz->getId()]),
                'questionsLength' => count($quiz->getQuestions()),
            ];
        }

        return new JsonResponse($quizzesArray);
    }


**/

/**

    // Method to fetch quizzes based on filter criteria

    #[Route('/fetch-filtered-quizzes', name: 'fetch_filtered_quizzes')]
    public function fetchFilteredQuizzes3(Request $request, QuizRepository $quizRepository, PaginatorInterface $paginator): JsonResponse
    {
        $trainerName = $request->query->get('trainer');
        $topic = $request->query->get('topic');
        $page = $request->query->getInt('page', 1);
        $limit = 10; // Define how many items  per page
        //$userId = $request->query->get('user');


        //$queryBuilder = $quizRepository->findByFiltersQueryBuilder($trainerName, $topic, $userId);
        $queryBuilder = $quizRepository->findByFiltersQueryBuilder($trainerName, $topic);
        $pagination = $paginator->paginate($queryBuilder, $page, $limit);

        $quizzesArray = [];
        foreach ($pagination as $quiz) {
            $quizzesArray[] = [
                'id' => $quiz->getId(),
                'creationDate' => $quiz->getCreationDate()->format('d-m-Y'),
                'type' => $quiz->getType(),
                'trainerName' => $quiz->getTrainer() ? $quiz->getTrainer()->getName() : 'N/A',
                'detailsUrl' => $this->generateUrl('app_quiz_details', ['id' => $quiz->getId()]),
                'historyUrl' => $this->generateUrl('app_quiz_history', ['id' => $quiz->getId()]),
                'questionsLength' => count($quiz->getQuestions()),
            ];
        }

        // Preparing pagination data
        $paginationData = [
            'currentPage' => $pagination->getCurrentPageNumber(),
            'totalPages' => ceil($pagination->getTotalItemCount() / $limit),
            'itemsPerPage' => $limit,
            'totalItems' => $pagination->getTotalItemCount(),
        ];

        return new JsonResponse([
            'quizzes' => $quizzesArray,
            'pagination' => $paginationData,
        ]);
    }




**/















    #[Route('/fetch-filtered-quizzes', name: 'fetch_filtered_quizzes')]
    public function fetchFilteredQuizzes(Request $request, QuizRepository $quizRepository, PaginatorInterface $paginator): JsonResponse
    {
        $trainerName = $request->query->get('trainer');
        $topic = $request->query->get('topic');
        $page = $request->query->getInt('page', 1);
        $limit = 10; // Define how many items  per page
        //$userId = $request->query->get('user');
        $userId = $request->query->get('user');

        $queryBuilder = $quizRepository->findByFiltersQueryBuilder($trainerName, $topic, $userId);
        //$queryBuilder = $quizRepository->findByFiltersQueryBuilder($trainerName, $topic);
        $pagination = $paginator->paginate($queryBuilder, $page, $limit);

        $quizzesArray = [];
        foreach ($pagination as $quiz) {
            $quizzesArray[] = [
                'id' => $quiz->getId(),
                'creationDate' => $quiz->getCreationDate()->format('d-m-Y'),
                'type' => $quiz->getType(),
                'trainerName' => $quiz->getTrainer() ? $quiz->getTrainer()->getName() : 'N/A',
                'detailsUrl' => $this->generateUrl('app_quiz_details', ['id' => $quiz->getId()]),
                'historyUrl' => $this->generateUrl('app_quiz_history', ['id' => $quiz->getId()]),
                'questionsLength' => count($quiz->getQuestions()),
            ];
        }

        // Preparing pagination data
        $paginationData = [
            'currentPage' => $pagination->getCurrentPageNumber(),
            'totalPages' => ceil($pagination->getTotalItemCount() / $limit),
            'itemsPerPage' => $limit,
            'totalItems' => $pagination->getTotalItemCount(),
        ];

        return new JsonResponse([
            'quizzes' => $quizzesArray,
            'pagination' => $paginationData,
        ]);
    }





}
