<?php

namespace App\Tests;

use App\Repository\FoodSafetyBestPracticesRepository;
use Doctrine\DBAL\Exception;
use PHPUnit\Framework\TestCase;
use App\Services\OpenAIService;
use Psr\Log\LoggerInterface;
use Symfony\Component\Dotenv\Dotenv;

class OpenAIServiceTest extends TestCase
{
    private OpenAIService $openAIService;

    protected function setUp(): void
    {
        // Load the environment variables from the .env file
        $dotenv = new Dotenv();
        $dotenv->loadEnv(__DIR__.'/../.env.local');

        // Mock the LoggerInterface
        $loggerMock = $this->createMock(LoggerInterface::class);

        // Get the API key from the environment
        $apiKey = $_ENV['OPENAI_API_KEY'];


        // Ensure the CACERT_PATH is set for the test environment
        $cacertPath = $_ENV['CACERT_PATH'];

        $this->assertTrue(file_exists($cacertPath), 'CACERT_PATH does not point to an existing file.');

        // Initialise the OpenAIService with the API key and the mocked logger
        $this->openAIService = new OpenAIService($apiKey, $loggerMock);
    }

    /**
     * @throws Exception
     */
    public function testApiConnectivity()
    {
           // Call the generateQuestion method with a sample question
        $response = $this->openAIService->generateQuestion('food safety');

        // Ensures the response is not empty
        $this->assertNotEmpty($response);

        // Check if the 'response' key exists in the array
        $this->assertArrayHasKey('response', $response);

        // Get the response content
        $responseContent = $response['response'];

        // Print the response for debugging
        echo "API Response: " . json_encode($responseContent) . PHP_EOL;
    }
}
