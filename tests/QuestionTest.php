<?php

namespace App\Tests;

use PHPUnit\Framework\TestCase;
use App\Entity\Question;
use App\Entity\Quiz;
use App\Entity\Option;

class QuestionTest extends TestCase
{
    // Test the question creation method
    public function testQuestionCreation()
    {
        $question = new Question();

        $this->assertNull($question->getQuiz());
        $this->assertNull($question->getQuestionText());
        $this->assertEmpty($question->getOptions());
    }

    // Test the question setters and getters
    public function testSettingAndGetters()
    {
        $question = new Question();
        $question->setQuestionText('What is Symfony?');

        $this->assertEquals('What is Symfony?', $question->getQuestionText());
    }

    // Test the quiz relationship
    public function testQuizRelationship()
    {
        $question = new Question();
        $quiz = new Quiz();

        $question->setQuiz($quiz);

        $this->assertSame($quiz, $question->getQuiz());
    }

    // Test the option management methods
    public function testOptionManagement()
    {
        $question = new Question();
        $option1 = new Option();
        $option2 = new Option();

        $question->addOption($option1);
        $question->addOption($option2);

        $this->assertCount(2, $question->getOptions());
        $this->assertContains($option1, $question->getOptions());
        $this->assertContains($option2, $question->getOptions());

        $question->removeOption($option1);

        $this->assertNotContains($option1, $question->getOptions());
        $this->assertContains($option2, $question->getOptions());
    }
}
