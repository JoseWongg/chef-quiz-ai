<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class HomeControllerTest extends WebTestCase
{
    /**
     * Test the homepage route and its structure.
     */
    public function testHomepageRouteAndStructure()
    {
        // Create a new client to make HTTP requests
        $client = static::createClient();
        // Request the homepage URL
        $crawler = $client->request('GET', '/');
        // Check that the HTTP status code is 200 (OK)
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        // Call a helper method to assert that the controller is HomeController
        $this->assertControllerIsHomeController($client);
    }

    /**
     * Helper method to assert that the controller is HomeControllerRRR.
     *
     * @param KernelBrowser $client The client instance.
     */
    private function assertControllerIsHomeController($client): void
    {
        // Check that the response is successful (HTTP status code 2xx)
        $this->assertTrue($client->getResponse()->isSuccessful());
    }

    /**
     * Test that the home template has the required structure.
     */
    public function testHomeTemplateStructure()
    {
        // Path to the Twig template file
        $templatePath = __DIR__ . '/../templates/home/index.html.twig';

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