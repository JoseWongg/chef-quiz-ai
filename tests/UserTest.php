<?php

namespace App\Tests;

use App\Entity\Quiz;
// Import the User entity class to be tested
use PHPUnit\Framework\TestCase;
// Import the TestCase class from PHPUnit
use App\Entity\User;
// Import the AssignedQuiz entity class to be tested
use App\Entity\AssignedQuiz;

class UserTest extends TestCase
{
    public function testUserCreation()
    {
        $user = new User();
        $this->assertNotNull($user->getRoles());
        $this->assertContains('ROLE_USER', $user->getRoles());
    }

    // Test the email and name setters and getters
    public function testEmailAndNameSettersAndGetters()
    {
        $user = new User();
        $user->setEmail('user@example.com');
        $user->setName('John Doe');

        $this->assertEquals('user@example.com', $user->getEmail());
        $this->assertEquals('John Doe', $user->getName());
    }

    // Test the role management methods
    public function testRoleManagement()
    {
        $user = new User();

        // Test default ROLE_USER is present
        $this->assertContains('ROLE_USER', $user->getRoles());

        // Test setting and getting custom roles
        $user->setRoles(['ROLE_ADMIN']);
        $this->assertContains('ROLE_ADMIN', $user->getRoles());
        $this->assertContains('ROLE_USER', $user->getRoles());
    }

    // Test the password management methods
    public function testPasswordManagement()
    {
        $user = new User();
        $user->setPlainPassword('plainPassword');
        $user->setPassword('hashedPassword');

        $this->assertEquals('plainPassword', $user->getPlainPassword());
        $this->assertEquals('hashedPassword', $user->getPassword());
    }

    // Test the quiz assignment method
    public function testAssignQuizToChef()
    {
        $trainer = new User();
        $chef = new User();
        $quiz = new Quiz();

        $assignedQuiz = new AssignedQuiz();
        $assignedQuiz->setQuiz($quiz);

        $trainer->assignQuizToChef($assignedQuiz, $chef);

        $this->assertContains($assignedQuiz, $trainer->getAssignedQuizzes());
        $this->assertContains($assignedQuiz, $chef->getReceivedQuizzes());
    }

    // Test the quiz removal method
    public function testRemoveCreatedQuiz()
    {
        $user = new User();
        $quiz = new Quiz();
        $user->addCreatedQuiz($quiz);
        $user->removeCreatedQuiz($quiz);

        $this->assertNotContains($quiz, $user->getCreatedQuizzes());
    }

    // Test the AssignedQuiz removal method
    public function testAddAndRemoveAssignedQuiz()
    {
        $user = new User();
        $assignedQuiz = new AssignedQuiz();

        $user->addAssignedQuiz($assignedQuiz);
        $this->assertContains($assignedQuiz, $user->getAssignedQuizzes());

        $user->removeAssignedQuiz($assignedQuiz);
        $this->assertNotContains($assignedQuiz, $user->getAssignedQuizzes());
    }

    // Test the addReceivedQuiz method
    public function testAddReceivedQuiz()
    {
        $user = new User();
        $assignedQuiz = new AssignedQuiz();

        $user->addReceivedQuiz($assignedQuiz);
        $this->assertContains($assignedQuiz, $user->getReceivedQuizzes());
    }
}
