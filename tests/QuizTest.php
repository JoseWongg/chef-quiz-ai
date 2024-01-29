<?php

namespace App\Tests;

use PHPUnit\Framework\TestCase;
use App\Entity\Quiz;
use App\Entity\User;
use App\Entity\Question;
use App\Entity\AssignedQuiz;

class QuizTest extends TestCase
{
    // Test the quiz creation method
    public function testQuizCreationAndDefaultValues()
    {
        $quiz = new Quiz();

        $this->assertFalse($quiz->isApproved());
        $this->assertEmpty($quiz->getQuestions());
    }

    // Test the quiz setters and getters
    public function testSettingAndGetters()
    {
        $quiz = new Quiz();
        $date = new \DateTime();

        $quiz->setType('Type');
        $quiz->setTitle('Title');
        $quiz->setCreationDate($date);
        $quiz->setCaseScenario('Case Scenario');

        $this->assertEquals('Type', $quiz->getType());
        $this->assertEquals('Title', $quiz->getTitle());
        $this->assertEquals($date, $quiz->getCreationDate());
        $this->assertEquals('Case Scenario', $quiz->getCaseScenario());
    }

    // Test the trainer relationship
    public function testTrainerRelationship()
    {
        $quiz = new Quiz();
        $trainer = new User();

        $quiz->setTrainer($trainer);

        $this->assertSame($trainer, $quiz->getTrainer());
    }

    // Test the question management methods
    public function testQuestionManagement()
    {
        $quiz = new Quiz();
        $question = new Question();

        $quiz->addQuestion($question);

        $this->assertCount(1, $quiz->getQuestions());
        $this->assertContains($question, $quiz->getQuestions());

        $quiz->removeQuestion($question);

        $this->assertNotContains($question, $quiz->getQuestions());
    }

    // Test the approval status
    public function testApprovalStatus()
    {
        $quiz = new Quiz();

        $quiz->setIsApproved(true);

        $this->assertTrue($quiz->isApproved());
    }

    // Test the AssignedQuiz creation method
    public function testCreateAssignedQuiz()
    {
        $quiz = new Quiz();
        $assignedQuiz = $quiz->createAssignedQuiz();

        $this->assertInstanceOf(AssignedQuiz::class, $assignedQuiz);
        $this->assertSame($quiz, $assignedQuiz->getQuiz());
    }
}
