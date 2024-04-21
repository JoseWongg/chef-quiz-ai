<?php

namespace App\Controller;

use App\Entity\AssignedQuiz;
use App\Repository\AssignedQuizRepository;
use App\Services\AssignedQuizPreviewFormattingService;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;

// Controller to handle the quizzes assigned to the currently logged-in user
class MyQuizzesController extends AbstractController
{
    // Method to display all the quizzes assigned to the currently logged-in user
    #[Route('/my/quizzes', name: 'app_my_quizzes')]
    public function index(EntityManagerInterface $entityManager): Response
    {

        $topics = [
            "Cross-contamination",
            "Cooking Food",
            "Chilling Food",
            "Allergens",
            "Cleaning",
            "Initial Food Safety Training"
        ];


        // The currently logged-in user
        $user = $this->getUser();

        // All the assigned quizzes in the database
        $assignedQuizRepository = $entityManager->getRepository(AssignedQuiz::class);
        $assignedQuizzes = $assignedQuizRepository->findBy(['chef' => $user]);
        return $this->render('my_quizzes/index.html.twig', [
            'assignedQuizzes' => $assignedQuizzes,
            'user' => $user,
            'topics' => $topics
        ]);
    }


    //Method to display the details of a quiz for the user to complete
    #[Route('/my/quizzes/{id}', name: 'app_my_quizzes_take_quiz')]
    public function takeQuiz($id, AssignedQuizRepository $assignedQuizRepository, AssignedQuizPreviewFormattingService $assignedQuizPreviewFormattingService): Response
    {

        $assignedQuiz = $assignedQuizRepository->find($id);


        if (!$assignedQuiz) {
            throw $this->createNotFoundException('The assigned quiz does not exist.');
        }
        $formattedDetails = $assignedQuizPreviewFormattingService->formatAssignedQuizDetails($assignedQuiz);

        return $this->render('my_quizzes/take_quiz.html.twig', [
            'assignedQuiz' => $assignedQuiz,
            'formattedDetails' => $formattedDetails,
        ]);
    }


    // Method to show the AssignedQuiz details
    #[Route('/my/quizzes/{id}/details', name: 'my_quiz_details', methods: ['GET'])]
    public function assignedQuizDetails($id, AssignedQuizRepository $assignedQuizRepository, AssignedQuizPreviewFormattingService $assignedQuizPreviewFormattingService): Response {
        $assignedQuiz = $assignedQuizRepository->find($id);
        if (!$assignedQuiz) {
            throw $this->createNotFoundException('The assigned quiz does not exist.');
        }

        $formattedDetails = $assignedQuizPreviewFormattingService->formatAssignedQuizDetails($assignedQuiz);

        return $this->render('my_quizzes/my_quiz_details.html.twig', [
            'assignedQuiz' => $assignedQuiz,
            'formattedDetails' => $formattedDetails,
        ]);
    }


    // Method to submit the quiz
    #[Route('/my/quizzes/{id}/submit', name: 'submit_quiz', methods: ['POST'])]
    public function submitQuiz(int $id, Request $request, EntityManagerInterface $entityManager, AssignedQuizRepository $assignedQuizRepository): Response {
        $data = json_decode($request->getContent(), true);
        $assignedQuiz = $assignedQuizRepository->find($id);

        // Retrieve the currently authenticated user
        $currentUser = $this->getUser();

        if (!$assignedQuiz) {
            return $this->json(['message' => 'Quiz not found'], Response::HTTP_NOT_FOUND);
        }

        // Check if the current user is the chef assigned to the quiz
        if ($assignedQuiz->getChef() !== $currentUser) {
            // Return an unauthorized response if the current user does not match the assigned chef
            return $this->json(['message' => 'Unauthorized access'], Response::HTTP_FORBIDDEN);
        }

        // Process responses and calculate the score
        try {
            $assignedQuiz->setCompleted(true)
                ->setCompletedDate(new \DateTime())
                ->setResponses($data['responses'])
                ->calculateScoreAndResult();

            $entityManager->persist($assignedQuiz);
            $entityManager->flush();

            // Return a simple success message
            return $this->json(['message' => 'Quiz submitted successfully'], Response::HTTP_OK);
        } catch (\Exception $e) {
            // Return an error response if an exception occurs
            return $this->json(['message' => 'An error occurred while submitting the quiz'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // Method to fetch the quizzes assigned to the currently logged-in user
    #[Route('/fetch-my-filtered-quizzes', name: 'fetch_my_filtered_quizzes')]
    public function fetchQuizzes(Request $request, AssignedQuizRepository $assignedQuizRepository, PaginatorInterface $paginator): JsonResponse {
        $completionFilter = $request->query->get('completion');
        $topicFilter = $request->query->get('topic');
        $page = $request->query->getInt('page', 1);
        //Define how many items  per page
        $limit = 10;

        $user = $this->getUser();
        $queryBuilder = $assignedQuizRepository->findByFiltersQueryBuilder($user, $completionFilter, $topicFilter);

        $pagination = $paginator->paginate($queryBuilder, $page, $limit);

        $serializedQuizzes = [];
        foreach ($pagination as $quiz) {
            $serializedQuizzes[] = [
                // Data for the AssignedQuiz
                'assignedQuizId' => $quiz->getId(),
                'assignedQuizMark' => $quiz->getMark(),
                'assignedQuizIsCompleted' => $quiz->isCompleted(),
                'assignedQuizIsPassed' => $quiz->isPassed(),
                // Data for the Quiz associated with the AssignedQuiz
                'generatedDate' => $quiz->getGeneratedDate()->format('d-m-Y'),
                'quizId' => $quiz->getQuiz()->getId(),
                'topic' => $quiz->getQuiz()->getType(),
                'assigner' => $quiz->getAssigner()->getName(),
                'deadline' => $quiz->getDeadline()->format('d-m-Y'),
                'overdue' => $quiz->calculateOverdueDays() > 0 ? 'Yes' : 'No',
                'mark' => $quiz->getMark(),
                'passed' => $quiz->isPassed() ? 'Passed' : 'Failed',
                'complete' => $quiz->isCompleted() ? 'Completed' : 'Incomplete',
            ];
        }

        $paginationData = [
            'currentPage' => $pagination->getCurrentPageNumber(),
            'totalPages' => ceil($pagination->getTotalItemCount() / $limit),
            'itemsPerPage' => $limit,
            'totalItems' => $pagination->getTotalItemCount(),
        ];

        return new JsonResponse([
            'quizzes' => $serializedQuizzes,
            'pagination' => $paginationData,
        ]);
    }
}


