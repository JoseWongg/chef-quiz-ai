<?php

namespace App\Tests;

use PHPUnit\Framework\TestCase;
use App\Entity\Option;
use App\Entity\Question;

class OptionTest extends TestCase
{
    // Test the option creation method
    public function testOptionCreation()
    {
        $option = new Option();

        $this->assertNull($option->getQuestion());
        $this->assertNull($option->getOptionText());
        $this->assertFalse($option->getIsCorrect());
        $this->assertNull($option->getFeedback());
        $this->assertFalse($option->getIsSelected());
    }

    // Test the option setters and getters
    public function testSettingAndGetters()
    {
        $option = new Option();

        $option->setOptionText('Option Text');
        $option->setIsCorrect(true);
        $option->setFeedback('Feedback Text');
        $option->setIsSelected(true);

        $this->assertEquals('Option Text', $option->getOptionText());
        $this->assertTrue($option->getIsCorrect());
        $this->assertEquals('Feedback Text', $option->getFeedback());
        $this->assertTrue($option->getIsSelected());
    }

    // Test the question relationship
    public function testQuestionRelationship()
    {
        $option = new Option();
        $question = new Question();

        $option->setQuestion($question);

        $this->assertSame($question, $option->getQuestion());
    }
}
