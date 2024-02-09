<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Services\OpenAIService;

class GenerateQuizzController extends AbstractController
{
    private OpenAIService $openAIService;

    public function __construct(OpenAIService $openAIService)
    {
        $this->openAIService = $openAIService;
    }

    #[Route('/generate/quizz', name: 'app_generate_quizz', methods: ['GET', 'POST'])]
    public function index(Request $request): Response | JsonResponse
    {
        if ($request->isMethod('POST')) {
            if ($request->isXmlHttpRequest()) { // Check if it is an AJAX request
                // Call the OpenAIService to generate the quiz content
                $responseContent = $this->openAIService->generateQuizCaseScenario();

                if (isset($responseContent['error'])) {
                    // Handle error here, return JSON response with error
                    return new JsonResponse(['error' => 'Error generating quiz. Please try again.']);
                } else {

                    // Parse the response to separate the scenario from the actions
                    $scenarioText = $this->parseScenarioText($responseContent['response']);

                    // Return JSON response with scenario text
                    return new JsonResponse(['scenarioText' => $scenarioText]);
                }
            } else {
                // Handle non-AJAX POST request
                // Render the initial template with error message
                return $this->render('generate_quizz/index.html.twig', [
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

    private function parseScenarioText(string $responseText): string
    {
        $start = strpos($responseText, "CASE SCENARIO: (") + strlen("CASE SCENARIO: (");
        $end = strpos($responseText, ")\n\nACTION 1:", $start);
        $scenarioText = substr($responseText, $start, $end - $start);

        return trim($scenarioText);
    }
}
