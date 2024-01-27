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

    public function updateProgression(): self
    {
        $this->progression = 0.0;
        $questions = $this->getQuiz()->getQuestions();
        $total = count($questions);
        $answered = 0;

        foreach ($questions as $question) {
            foreach ($question->getOptions() as $option) {
                if ($option->getIsSelected()) {
                    $answered++;
                    break;
                }
            }
        }

        if ($total > 0) {
            $this->progression = $answered / $total;
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

    public function submit(): self
    {
        $this->completed = true;
        $this->completedDate = new \DateTime();
        $this->calculateMark();
        return $this;
    }




}
