<?php

namespace App\Tests;

use PHPUnit\Framework\TestCase;
use App\Services\OpenAIService;
use Symfony\Component\Dotenv\Dotenv;
use Psr\Log\LoggerInterface;
class OpenAIServiceTest extends TestCase
{
    private OpenAIService $openAIService;


    protected function setUp(): void
    {
        $dotenv = new Dotenv();
        $dotenv->loadEnv(__DIR__.'/../../.env.local');


        $apiKey = $_ENV['OPENAI_API_KEY'];
        $loggerMock = $this->createMock(LoggerInterface::class);

        $this->openAIService = new OpenAIService($apiKey, $loggerMock);
    }

    public function testApiConnectivity()
    {
        // Test API connectivity
        $prompt = "food safety";
        $response = $this->openAIService->generateQuestion($prompt);

        // Ensure the response is not empty
        $this->assertNotEmpty($response);

        // Check if the 'response' key exists in the array
        $this->assertArrayHasKey('response', $response);

        // Get the response content
        $responseContent = $response['response'];

        // Print the response
        echo "API Response: " . json_encode($responseContent) . PHP_EOL;
    }
}