<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Log\LoggerInterface;

//  OpenAIService class to handle the API requests to OpenAI
class OpenAIService
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
    }


    //  Method to generate the quiz content
    public function generateQuizContent(string $prompt): array
    {
        $url = 'https://api.openai.com/v1/chat/completions';

        try {
            $response = $this->client->request('POST', $url, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'model' => 'gpt-4',
                    'messages' => [
                        ["role" => "system", "content" => "You are a helpful assistant designed to generate a quiz."],
                        ["role" => "user", "content" => $prompt],
                    ],
                ],
            ]);

            $body = $response->getBody();
            $content = json_decode($body->getContents(), true);

            return ['response' => $content['choices'][0]['message']['content']] ?? [];
        } catch (GuzzleException $e) {
            return ['error' => $e->getMessage()];
        }
    }


    //  Method to generate the quiz case scenario
    public function generateQuizCaseScenario(): array // Removed $prompt parameter since we're using a fixed prompt here
    {
        $url = 'https://api.openai.com/v1/chat/completions';

        $customPrompt = "Generate a case scenario related to food safety in a commercial kitchen. The scenario should involve five actions by a chef that violate food safety practices. Keep the description under 500 words. The case scenario should start with the title: 'CASE SCENARIO' followed by the actual case scenario text within brackets. After the case scenario text, list the five actions violating the food safety in the case scenario following this format:
ACTION 1: ['Food safety violation 1 here'].
ACTION 2: ['Food safety violation 2 here'].
ACTION 3: ['Food safety violation 3 here'].
ACTION 4: ['Food safety violation 4 here'].
ACTION 5: ['Food safety violation 5 here'].";

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

            return ['response' => $content['choices'][0]['message']['content']] ?? [];
        } catch (GuzzleException $e) {
            return ['error' => $e->getMessage()];
        }
    }
}