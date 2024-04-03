<?php

namespace App\Tests;

use PHPUnit\Framework\TestCase;
use App\Entity\AssignedQuiz;
use App\Entity\Quiz;
use App\Entity\User;
use App\Entity\Question;
use App\Entity\Option;

class AssignedQuizTest extends TestCase
{
    public function testAssignedQuizCreation()
    {
        $assignedQuiz = new AssignedQuiz();

        $this->assertNull($assignedQuiz->getQuiz());
        $this->assertNull($assignedQuiz->getAssigner());
        $this->assertNull($assignedQuiz->getChef());
        $this->assertNull($assignedQuiz->getGeneratedDate());
        $this->assertNull($assignedQuiz->getDeadline());
        $this->assertNull($assignedQuiz->getCompletedDate());
        $this->assertNull($assignedQuiz->getMark());
        $this->assertFalse($assignedQuiz->isCompleted());
        $this->assertEquals(0.0, $assignedQuiz->getProgression());
    }

    public function testSettingAndGetters()
    {
        $assignedQuiz = new AssignedQuiz();
        $quiz = new Quiz();
        $assigner = new User();
        $chef = new User();
        $generatedDate = new \DateTime();
        $deadline = new \DateTime('+1 day');

        $assignedQuiz->setQuiz($quiz);
        $assignedQuiz->setAssigner($assigner);
        $assignedQuiz->setChef($chef);
        $assignedQuiz->setGeneratedDate($generatedDate);
        $assignedQuiz->setDeadline($deadline);
        $assignedQuiz->setMark(80.0);
        $assignedQuiz->setCompleted(true);

        $this->assertSame($quiz, $assignedQuiz->getQuiz());
        $this->assertSame($assigner, $assignedQuiz->getAssigner());
        $this->assertSame($chef, $assignedQuiz->getChef());
        $this->assertEquals($generatedDate, $assignedQuiz->getGeneratedDate());
        $this->assertEquals($deadline, $assignedQuiz->getDeadline());
        $this->assertEquals(80.0, $assignedQuiz->getMark());
        $this->assertTrue($assignedQuiz->isCompleted());
    }
    public function testProgressionCalculation()
    {
        // Create a quiz with questions and options
        $quiz = new Quiz();
        for ($i = 0; $i < 5; $i++) {
            $question = new Question();
            $option = new Option();
            //$option->setIsSelected($i < 2); // Select the first 2 questions
            $question->addOption($option);
            $quiz->addQuestion($question);
        }

        $assignedQuiz = new AssignedQuiz();
        $assignedQuiz->setQuiz($quiz);

        // Call the updateProgression method to calculate progression
        $assignedQuiz->updateProgression();

        // Debugging: Output the actual progression value
        var_dump($assignedQuiz->getProgression());

        // Now, assert the progression value
        $this->assertEquals(0.4, $assignedQuiz->getProgression()); // 2 out of 5 questions have selected options
    }

    public function testMarkCalculationAndSubmission()
    {
        // Create a quiz with questions and options
        $quiz = new Quiz();
        for ($i = 0; $i < 5; $i++) {
            $question = new Question();
            $option = new Option();
            //$option->setIsSelected(true);
            $option->setIsCorrect($i % 2 === 0); // Correct alternate options
            $question->addOption($option);
            $quiz->addQuestion($question);
        }

        $assignedQuiz = new AssignedQuiz();
        $assignedQuiz->setQuiz($quiz);
        $assignedQuiz->submit();

        $this->assertEquals(60.0, $assignedQuiz->getMark()); // 3 out of 5 questions correct
        $this->assertTrue($assignedQuiz->isCompleted());
        $this->assertNotNull($assignedQuiz->getCompletedDate());
    }

    public function testLateSubmission()
    {
        $assignedQuiz = new AssignedQuiz();
        $assignedQuiz->setDeadline(new \DateTime('-1 day'));

        $this->assertTrue($assignedQuiz->isLate());
    }
}