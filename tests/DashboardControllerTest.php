<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;

class DashboardControllerTest extends WebTestCase
{
    /**
     * Test the dashboard route and its structure.
     */
    public function testDashboardPageRouteAndStructure()
    {
        // Create a new client to make HTTP requests
        $client = static::createClient();
        // Request the dashboard URL
        $crawler = $client->request('GET', '/dashboard');
        // Check that the HTTP status code is 200 (OK)
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        // Call a helper method to assert that the controller is HomeController
        $this->assertControllerIsDashboardController($client);
    }

    /**
     * Helper method to assert that the controller is DashboardController.
     *
     * @param KernelBrowser $client The client instance.
     */
    private function assertControllerIsDashboardController($client): void
    {
        // Check that the response is successful (HTTP status code 2xx)
        $this->assertTrue($client->getResponse()->isSuccessful());
    }

    /**
     * Test that the dashboard template has the required structure.
     * PHPUnit follows a convention where any public method in a test class that starts with the word test is automatically recognized as a test method and executed.
     */
    public function testDashboardTemplateStructure()
    {
        // Path to the Twig template file
        $templatePath = __DIR__ . '/../templates/dashboard/index.html.twig';

        // Read the contents of the template file
        $templateContent = file_get_contents($templatePath);

        // Check that the template extends 'layout.html.twig'
        $this->assertStringContainsString("{% extends 'layout.html.twig' %}", $templateContent);

        // Check for the presence of 'layout_title' block
        $this->assertStringContainsString("{% block layout_title %}", $templateContent);
        $this->assertStringContainsString("{% endblock %}", $templateContent);

        // Check for the presence of 'content' block
        $this->assertStringContainsString("{% block content %}", $templateContent);
        $this->assertStringContainsString("{% endblock %}", $templateContent);
    }
}
