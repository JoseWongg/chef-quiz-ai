<?php

namespace App\Entity;

use App\Repository\AssignedQuizRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: AssignedQuizRepository::class)]
#[ORM\Table(name: 'assigned_quiz')]
class AssignedQuiz
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type:'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Quiz::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotBlank]
    private ?Quiz $quiz = null;


    // Trainer who assigned the quiz
    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: "assignedQuizzes")]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotBlank]
    private ?User $assigner = null;

    // Chef who received the quiz
    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: "receivedQuizzes")]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotBlank]
    private ?User $chef = null;

    #[ORM\Column(type: "datetime")]
    private ?\DateTimeInterface $generatedDate = null;

    #[ORM\Column(type: "datetime")]
    private ?\DateTimeInterface $deadline = null;

    #[ORM\Column(type: "datetime", nullable: true)]
    private ?\DateTimeInterface $completedDate = null;

    #[ORM\Column(type: "float", nullable: true)]
    private ?float $mark = null;

    #[ORM\Column(type: "boolean")]
    private bool $completed = false;

    #[ORM\Column(type: "float")]
    private float $progression = 0.0;

    // The number of correct answers to pass the quiz
    #[ORM\Column(type: "integer")]
    private ?int $passingScore = null;
    #[ORM\Column(type: "json", nullable: true)]
    private array $responses = [];



    public function getResponses(): ?array
    {
        return $this->responses;
    }

    public function setResponses(array $responses): self
    {
        $this->responses = $responses;

        return $this;
    }

    public function getPassingScore(): ?int
    {
        return $this->passingScore;
    }

    public function setPassingScore(int $passingScore): self
    {
        $this->passingScore = $passingScore;
        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }


    public function getQuiz(): ?Quiz
    {
        return $this->quiz;
    }

    public function setQuiz(?Quiz $quiz): self
    {
        $this->quiz = $quiz;

        return $this;
    }

    public function getAssigner(): ?User
    {
        return $this->assigner;
    }

    public function setAssigner(?User $assigner): self
    {
        $this->assigner = $assigner;

        return $this;
    }

    public function getChef(): ?User
    {
        return $this->chef;
    }

    public function setChef(?User $chef): self
    {
        $this->chef = $chef;

        return $this;
    }

    public function getGeneratedDate(): ?\DateTimeInterface
    {
        return $this->generatedDate;
    }

    public function setGeneratedDate(\DateTimeInterface $generatedDate): self
    {
        $this->generatedDate = $generatedDate;
        return $this;
    }

    public function getDeadline(): ?\DateTimeInterface
    {
        return $this->deadline;
    }

    public function setDeadline(\DateTimeInterface $deadline): self
    {
        $this->deadline = $deadline;
        return $this;
    }

    public function getCompletedDate(): ?\DateTimeInterface
    {
        return $this->completedDate;
    }

    public function setCompletedDate(?\DateTimeInterface $completedDate): self
    {
        $this->completedDate = $completedDate;
        return $this;
    }

    public function getMark(): ?float
    {
        return $this->mark;
    }

    public function setMark(float $mark): self
    {
        $this->mark = $mark;

        return $this;
    }

    public function isCompleted(): ?bool
    {
        return $this->completed;
    }

    public function setCompleted(bool $completed): self
    {
        $this->completed = $completed;

        return $this;
    }

    public function getProgression(): ?float
    {
        return $this->progression;
    }

    public function setProgression(float $progression): self
    {
        $this->progression = $progression;

        return $this;
    }

    public function updateProgression2(): self
    {
        $this->progression = 0.0;
        $questions = $this->getQuiz()->getQuestions();
        $totalQuestions = count($questions);
        $totalSelectedOptions = 0;

        foreach ($questions as $question) {
            foreach ($question->getOptions() as $option) {
                if ($option->getIsSelected()) {
                    $totalSelectedOptions++;
                }
            }
        }

        if ($totalQuestions > 0) {
            $this->progression = $totalSelectedOptions / ($totalQuestions * count($question->getOptions()));
        }

        return $this;
    }

    public function calculateMark(): self
    {
        $this->mark = 0.0;
        $questions = $this->getQuiz()->getQuestions();
        $total = count($questions);
        $correct = 0;

        if ($total > 0) {
            foreach ($questions as $question) {
                foreach ($question->getOptions() as $option) {
                    if ($option->getIsSelected() && $option->getIsCorrect()) {
                        $correct++;
                        break;
                    }
                }
            }
            $this->mark = ($correct / $total) * 100;
        }

        return $this;
    }

    public function isLate(): bool
    {
        return $this->deadline < new \DateTime();
    }


    public function calculateScoreAndResult():self
    {
        $this->mark = 0.0;
        $questions = $this->getQuiz()->getQuestions();
        $totalQuestions = count($questions);
        $correctAnswersCount = 0;

        foreach ($this->responses as $response) {
            // Find the corresponding question
            $question = $questions->filter(function($q) use ($response) {
                return $q->getId() == $response['questionId'];
            })->first();

            if (!$question) {
                continue; // Skip if the question is not found
            }

            // Determine the index of the correct option
            $correctOptionIndex = null;
            foreach ($question->getOptions() as $index => $option) {
                if ($option->getIsCorrect()) {
                    //$correctOptionIndex = $index + 1;
                    $correctOptionIndex = $index;
                    break;
                }
            }

            // Check if the selected option matches the correct option
            $selectedOptionIndex = substr($response['selectedOptionId'], -1); // Extract the last character, which is the index
            if ($correctOptionIndex == $selectedOptionIndex) {
                $correctAnswersCount++;
            }
        }

        // Calculate the mark as a percentage
        if ($totalQuestions > 0) {
            $this->mark = round(($correctAnswersCount / $totalQuestions) * 100,2);
        }

        return $this;
    }


    public function calculateOverdueDays(): int {
        $now = new \DateTime(); // Current date and time
        if ($this->deadline >= $now) {
            // Quiz is not overdue, or it's the deadline day. Return 0 or negative days remaining.
            return (int)$this->deadline->diff($now)->format('%r%a');
        } else {
            // Quiz is overdue. Return positive days overdue.
            return (int)$now->diff($this->deadline)->format('%a');
        }
    }


    public function updateProgression(): self {
        $totalQuestions = count($this->getQuiz()->getQuestions());
        $answeredQuestions = count($this->responses);
        $this->progression = ($totalQuestions > 0) ? ($answeredQuestions / $totalQuestions) * 100 : 0;
        return $this;
    }


    public function addResponseAndUpdateProgression(array $response): self {
        $this->responses[] = $response;
        $this->updateProgression();
        return $this;
    }


    public function submit(): self {
        $this->completed = true;
        $this->completedDate = new \DateTime();
        $this->calculateScoreAndResult();

        return $this;
    }

    // returns the selected options for each question
    public function getSelectedOptions(): array {
        $selectedOptions = [];
        foreach ($this->responses as $response) {
            $selectedOptions[$response['questionId']] = $response['selectedOptionId'];
        }
        return $selectedOptions;
    }




    // Giving the index in the responses array, returns the selected option id
    public function getSelectedOptionId(int $index): ?int
    {
        $response = $this->responses[$index] ?? null;
        if (!$response) {
            return null;
        }

        // Extract the number from "selectedOptionId": "optionXX"
        $selectedOptionIdString = $response['selectedOptionId'];
        preg_match('/option(\d+)/', $selectedOptionIdString, $matches);

        // Get the quiz associated with the assigned quiz
        $quiz = $this->getQuiz();
        // Get the questions array from the quiz
        $questions = $quiz->getQuestions()->toArray();
        // Get the question at the $index position
        $question = $questions[$index];
        // Get the options collection from the question
        $options = $question->getOptions();

        // Get the option at the specified index
        $selectedOption = $options->get($matches[1] ?? null);
        // If the option is null or doesn't have an ID, return null
        if (!$selectedOption || !$selectedOption->getId()) {
            return null;
        }

        // Return the ID of the selected option
        return $selectedOption->getId();
    }

    // Returns true if the AssignedQuiz is passed
    public function isPassed(): bool {

        // The number of questions in the quiz
        $totalQuestions = count($this->getQuiz()->getQuestions());
        // The value in percentage of each question
        $questionValue = 100 / $totalQuestions;
        // The passing score. The number of correct answers to pass the quiz
        $passingScore = $this->passingScore;
        // The passing score in percentage
        $passingScorePercentage = $passingScore * $questionValue;
        // If the mark is greater or equal to the passing score in percentage, the quiz is passed
        return $this->mark >= $passingScorePercentage;
    }
}