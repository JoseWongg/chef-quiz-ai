<?php

namespace App\Services;

use App\Entity\FoodSafetyBestPractices;
use App\Repository\FoodSafetyBestPracticesRepository;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\NonUniqueResultException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Log\LoggerInterface;


//  OpenAIService class to handle the API requests to OpenAI (THIS DOES NOT USE A RAG APPROACH)
/*class OpenAIService
{
    //  GuzzleHttp Client instance
    private Client $client;
    //  OpenAI API key
    private string $apiKey;


    //  Constructor to initialise the GuzzleHttp Client and the API key
    public function __construct(string $apiKey, LoggerInterface $logger)
    {
        // Get the cacert.pem file path from environment variable
        $cacertPath = $_ENV['CACERT_PATH'] ?? null;

        if (!$cacertPath || !file_exists($cacertPath)) {
            $logger->error('CA bundle file not found at: '.$cacertPath);
            throw new \RuntimeException('CA bundle file not found at: '.$cacertPath);
        }

        $this->client = new Client([
            'verify' => $cacertPath
        ]);

        $this->apiKey = $apiKey;

        $this->logger = $logger;
    }


    //  Method to generate a quiz question
    public function generateQuestion(STRING $topic): array
    {
        $url = 'https://api.openai.com/v1/chat/completions';

        $customPrompt = <<<PROMPT
Generate a case scenario related to food safety, particularly to the topic "{$topic}", that includes exactly one action in breach of good practices. The scenario should be followed by a question aimed at identifying the breach action, four answer options (one correct and three incorrect), and feedback for each option explaining why it is right or wrong. Structure the response with clear delimiters as follows:

<<<Case Scenario>>>
[Case Scenario]
<<<End Case Scenario>>>

<<<Question>>>
[Question]
<<<End Question>>>

<<<Options>>>
<<<Option 1>>>
[Text] | Right/Wrong | [Feedback]
<<<End Option 1>>>
<<<Option 2>>>
[Text] | Right/Wrong | [Feedback]
<<<End Option 2>>>
<<<Option 3>>>
[Text] | Right/Wrong | [Feedback]
<<<End Option 3>>>
<<<Option 4>>>
[Text] | Right/Wrong | [Feedback]
<<<End Option 4>>>
<<<End Options>>>

Ensure the scenario is suitable for educational purposes and does not exceed 200 words.
PROMPT;

        try {
            $response = $this->client->request('POST', $url, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'model' => 'gpt-4',
                    'messages' => [
                        ["role" => "system", "content" => "You are a helpful assistant designed to generate a detailed case scenario and actions for a food safety quiz in a commercial kitchen setting."],
                        ["role" => "user", "content" => $customPrompt],
                    ],
                ],
            ]);



            $body = $response->getBody();
            $content = json_decode($body->getContents(), true);


            $this->logger->info('API response received:', ['response' => $content]);


            return ['response' => $content['choices'][0]['message']['content']] ?? [];
        } catch (GuzzleException $e) {
            return ['error' => $e->getMessage()];
        }
    }
}

*/

// OpenAIService class to handle the API requests to OpenAI (THIS USES A RAG APPROACH)
class OpenAIService
{
    private Client $client;
    private string $apiKey;
    private LoggerInterface $logger;


    // FoodSafetyBestPracticesRepository instance to retrieve the best practices from the database
    private FoodSafetyBestPracticesRepository $foodSafetyBestPracticesRepository;

    // Constructor to initialise the GuzzleHttp Client, the API key, the logger, and the FoodSafetyBestPracticesRepository
    public function __construct(string $apiKey, LoggerInterface $logger, FoodSafetyBestPracticesRepository $foodSafetyBestPracticesRepository)
    {
        // Get the cacert.pem file path from environment variable
        $cacertPath = $_ENV['CACERT_PATH'] ?? null;
        if (!$cacertPath || !file_exists($cacertPath)) {
            $logger->error('CA bundle file not found at: ' . $cacertPath);
            throw new \RuntimeException('CA bundle file not found at: ' . $cacertPath);
        }
        $this->client = new Client([
            'verify' => $cacertPath
        ]);

        $this->apiKey = $apiKey;
        $this->logger = $logger;
        $this->foodSafetyBestPracticesRepository = $foodSafetyBestPracticesRepository;
    }

    /**
     *
     * @throws Exception
     * This method generates a quiz question using the OpenAI API
     */
    public function generateQuestion(string $topic): array
    {
        // Define the API endpoint
        $url = 'https://api.openai.com/v1/chat/completions';

        // Get a random rule from the database. Uses a custom method in the repository to execute a raw SQL query
        $rule = $this->foodSafetyBestPracticesRepository->findRandomRuleByTopic($topic);

        // Get the contextual statement from the rule to add to the prompt
        $contextualStatement = $rule ? $rule->getRule() : '';

        // Ensures the contextual statement is correctly included in the prompt
        $instruction = !empty($contextualStatement) ? "We are testing professional chefs about their knowledge of the following rule: \"$contextualStatement\". " : "";


        $customPrompt = <<<PROMPT
{$instruction} .Generate a case scenario portraying a breach of this rule. The scenario should be followed by a question aimed at identifying the breach action, four answer options (one correct and three incorrect), and feedback for each option explaining why it is right or wrong. Structure the response with clear delimiters as follows:

<<<Case Scenario>>>
[Case Scenario]
<<<End Case Scenario>>>

<<<Question>>>
[Question]
<<<End Question>>>

<<<Options>>>
<<<Option 1>>>
[Text] | Right/Wrong | [Feedback]
<<<End Option 1>>>
<<<Option 2>>>
[Text] | Right/Wrong | [Feedback]
<<<End Option 2>>>
<<<Option 3>>>
[Text] | Right/Wrong | [Feedback]
<<<End Option 3>>>
<<<Option 4>>>
[Text] | Right/Wrong | [Feedback]
<<<End Option 4>>>
<<<End Options>>>

Ensure the scenario is suitable for educational purposes and does not exceed 200 words.
Ensure you do not provide any clues of the correct answer in the scenario or the question.
PROMPT;

        try {
            $response = $this->client->request('POST', $url, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'model' => 'gpt-4',
                    'messages' => [
                        ["role" => "system", "content" => "You are a helpful assistant designed to generate a detailed case scenario and actions for a food safety quiz in a commercial kitchen setting."],
                        ["role" => "user", "content" => $customPrompt],
                    ],
                ],
            ]);

            $body = $response->getBody();
            $content = json_decode($body->getContents(), true);

            $this->logger->info('API response received:', ['response' => $content]);
            return ['response' => $content['choices'][0]['message']['content']] ?? [];
        } catch (GuzzleException $e) {
            return ['error' => $e->getMessage()];
        }
    }
}




