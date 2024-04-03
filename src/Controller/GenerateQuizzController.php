<?php

namespace App\Controller;

use App\Entity\Option;
use App\Entity\Question;
use App\Entity\Quiz;
use Doctrine\DBAL\Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Services\OpenAIService;
use Doctrine\ORM\EntityManagerInterface;


class GenerateQuizzController extends AbstractController
{
    // Method to generate a quiz question, scenario and options using the OpenAIService
    /**
     * @throws Exception
     */
    #[Route('/generate/quiz', name: 'app_generate_quiz', methods: ['GET', 'POST'])]
    public function index(Request $request, OpenAIService $openAIService): Response|JsonResponse
    {
        if ($request->isMethod('POST')) {

            $topic = $request->request->get('topic');
            // Check if it is an AJAX request
            if ($request->isXmlHttpRequest()) {

                // Call the OpenAIService to generate the quiz content
                $responseContent = $openAIService->generateQuestion($topic);

                if (isset($responseContent['error'])) {
                    // Handle error here, return JSON response with error
                    return new JsonResponse(['error' => 'Error generating question. Please try again.']);
                } else {

                    // Parse the response to separate the scenario from the actions
                    $scenarioText = $this->parseApiResponse($responseContent['response']);

                    // Return JSON response with scenario text
                    return new JsonResponse(['scenarioText' => $scenarioText]);
                }
            } else {
                // Handle non-AJAX POST request
                // Render the initial template with error message
                return $this->render('generate_quiz/index.html.twig', [
                    'controller_name' => 'GenerateQuizzController',
                    'error' => 'Invalid request. Please try again.'
                ]);
            }
        }

        // Render the initial template for GET requests or non-AJAX POST requests
        return $this->render('generate_quizz/index.html.twig', [
            'controller_name' => 'GenerateQuizzController',
        ]);
    }


    //  Method to parse the API response and extract the scenario text, question, and options
    private function parseApiResponse(string $apiResponse): array
    {
        // Define a helper function to extract text between two delimiters
        $extractBetween = function ($string, $start, $end) {
            $ini = strpos($string, $start);
            if ($ini === false) return '';
            $ini += strlen($start);
            $len = strpos($string, $end, $ini) - $ini;
            return $len > 0 ? substr($string, $ini, $len) : '';
        };

        // Extract the scenario
        $scenario = $extractBetween($apiResponse, "<<<Case Scenario>>>", "<<<End Case Scenario>>>");
        // Return an error if the scenario is empty
        if (empty(trim($scenario))) {
            return ['error' => 'Error generating question. Please try again.'];
        }

        // Extract the question
        $question = $extractBetween($apiResponse, "<<<Question>>>", "<<<End Question>>>");
        // Return an error if the question is empty
        if (empty(trim($question))) {
            return ['error' => 'Error generating question. Please try again.'];
        }

        // Extract and parse options
        $options = [];
        // Initialise a counter to keep track of the number of correct options
        $rightCount = 0;
        for ($i = 1; $i <= 4; $i++) {
            $optionContent = $extractBetween($apiResponse, "<<<Option $i>>>", "<<<End Option $i>>>");
            if (!empty($optionContent)) {

                // Directly extract the text, correctness, and feedback without assuming a specific format with brackets
                list($text, $rightWrongFeedback) = explode("|", $optionContent, 2);
                list($correctness, $feedback) = explode("|", $rightWrongFeedback, 2);

                // Trim for safety and remove leading/trailing spaces
                $text = trim($text);
                $correctness = trim($correctness);
                // Also trim periods and spaces from feedback
                $feedback = trim($feedback, " .");

                // Increment the counter if the option is marked as "Right"
                if ($correctness === 'Right') {
                    $rightCount++;
                }

                $options[] = [
                    'text' => $text,
                    'correctness' => $correctness === 'Right' ? 'Right' : 'Wrong',
                    'feedback' => $feedback,
                ];
            } else {
                // Return an error if any option is empty
                return ['error' => 'Error generating question. Please try again.'];
            }
        }

        // Validate that exactly one option is marked as "Right"
        if ($rightCount !== 1) {
            // Return an error if the condition is not met
            return [
                'error' => 'Error generating question. Please try again..'
            ];
        }

        return [
            'scenario' => trim($scenario),
            'question' => trim($question),
            'options' => $options,
        ];
    }



    // Method to save the generated question, options and quiz to the database
    #[Route('/generate/quiz/save', name: 'app_generate_quiz_save', methods: ['POST'])]
    public function saveQuiz(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $quizData = json_decode($request->getContent(), true);

        // Create a new Quiz entity
        $quiz = new Quiz();
        $quiz->setType($quizData['quizType']);
        // The User in the security context is the current logged-in user
        $quiz->setTrainer($this->getUser());
        $quiz->setCreationDate(new \DateTime());

        foreach ($quizData['questions'] as $questionData) {
            // Create and populate the Question entity
            $question = new Question();
            $question->setQuiz($quiz);
            $question->setQuestionText($questionData['question']);
            $question->setCaseScenario($questionData['scenario']);

            foreach ($questionData['options'] as $optionData) {
                // Create and populate the Option entity
                $option = new Option();
                $option->setQuestion($question);
                $option->setOptionText($optionData['optionText']);
                $option->setIsCorrect($optionData['isCorrect']);
                $option->setFeedback($optionData['feedback']);

                // Associate the Option with the Question
                $question->addOption($option);
            }

            // Associate the Question with the Quiz
            $quiz->addQuestion($question);
        }

        // Persist the Quiz entity (this will also persist the related questions and options due to cascade={"persist"})
        $entityManager->persist($quiz);
        $entityManager->flush();

        // Return a JSON response
        return new JsonResponse(['status' => 'success', 'message' => 'Quiz saved successfully']);
    }
}





