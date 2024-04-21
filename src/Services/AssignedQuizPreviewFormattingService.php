<?php

namespace App\Services;

use App\Entity\AssignedQuiz;
use App\Repository\OptionRepository;
use App\Repository\QuestionRepository;

/**
 * This class is used to format the details of an assigned quiz for display.
 */
class AssignedQuizPreviewFormattingService
{
    private QuestionRepository $questionRepository;
    private OptionRepository $optionRepository;

    public function __construct(QuestionRepository $questionRepository, OptionRepository $optionRepository)
    {
        $this->questionRepository = $questionRepository;
        $this->optionRepository = $optionRepository;
    }

    public function formatAssignedQuizDetails(AssignedQuiz $assignedQuiz): array {
        $formattedDetails = [];

        foreach ($assignedQuiz->getQuiz()->getQuestions() as $question) {
            $questionDetails = [
                'id' => $question->getId(),
                'caseScenario' => $question->getCaseScenario(),
                'questionText' => $question->getQuestionText(),
                'options' => [],
            ];

            foreach ($question->getOptions() as $option) {
                $optionDetails = [
                    'optionText' => $option->getOptionText(),
                    'isCorrect' => $option->getIsCorrect(),
                    'feedback' => $option->getFeedback(),
                    'id' => $option->getId(),
                ];


                // Check if this option was selected
                foreach ($assignedQuiz->getResponses() as $response) {
                    if ($response['questionId'] == $question->getId() && $response['selectedOptionId'] == $option->getId()) {
                        $questionDetails['selectedOption'] = $optionDetails + ['isSelected' => true];
                        break;
                    }
                }


                $questionDetails['options'][] = $optionDetails;
            }

            $formattedDetails[] = $questionDetails;
        }

        return $formattedDetails;
    }
}