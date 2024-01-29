<?php

namespace App\Entity;

use App\Repository\QuizRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: QuizRepository::class)]
#[ORM\Table(name: 'quiz')]
class Quiz
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    private ?string $type = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    private ?string $title = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: "createdQuizzes")]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $trainer = null;

    #[ORM\Column(type: "datetime")]
    #[Assert\NotBlank]
    private ?\DateTimeInterface $creationDate = null;

    #[ORM\Column(type: "text")]
    #[Assert\NotBlank]
    private ?string $caseScenario = null;


    #[ORM\OneToMany(mappedBy: "quiz", targetEntity: Question::class, cascade: ["persist"], orphanRemoval: true)]
    private Collection $questions;

    #[ORM\Column(type: "boolean")]
    private bool $isApproved = false;

    public function __construct()
    {
        $this->questions = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): self
    {
        $this->type = $type;
        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    public function getTrainer(): ?User
    {
        return $this->trainer;
    }

    public function setTrainer(?User $trainer): self
    {
        $this->trainer = $trainer;
        return $this;
    }

    public function getCreationDate(): ?\DateTimeInterface
    {
        return $this->creationDate;
    }

    public function setCreationDate(\DateTimeInterface $creationDate): self
    {
        $this->creationDate = $creationDate;
        return $this;
    }

    public function getCaseScenario(): ?string
    {
        return $this->caseScenario;
    }

    public function setCaseScenario(string $caseScenario): self
    {
        $this->caseScenario = $caseScenario;
        return $this;
    }

    /**
     * @return Collection|Question[]
     */
    public function getQuestions(): Collection
    {
        return $this->questions;
    }

    public function addQuestion(Question $question): self
    {
        if (!$this->questions->contains($question)) {
            $this->questions[] = $question;
            $question->setQuiz($this);
        }
        return $this;
    }

    public function removeQuestion(Question $question): self
    {
        if ($this->questions->removeElement($question)) {
            // set the owning side to null (unless already changed)
            if ($question->getQuiz() === $this) {
                $question->setQuiz(null);
            }
        }
        return $this;
    }

    public function isApproved(): bool
    {
        return $this->isApproved;
    }

    public function setIsApproved(bool $isApproved): self
    {
        $this->isApproved = $isApproved;
        return $this;
    }

    public function createAssignedQuiz(): AssignedQuiz
    {
        $assignedQuiz = new AssignedQuiz();
        $assignedQuiz->setQuiz($this);
        $assignedQuiz->setGeneratedDate(new \DateTime());

        return $assignedQuiz;
    }
}
