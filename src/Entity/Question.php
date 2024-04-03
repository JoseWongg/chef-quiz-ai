<?php

namespace App\Entity;

use App\Repository\QuestionRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


#[ORM\Entity(repositoryClass: QuestionRepository::class)]
#ORM\Table(name: 'question')
class Question
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    public function getCaseScenario(): ?string
    {
        return $this->caseScenario;
    }

    public function setCaseScenario(?string $caseScenario): void
    {
        $this->caseScenario = $caseScenario;
    }

    #[ORM\ManyToOne(targetEntity: Quiz::class, inversedBy: "questions")]
    #[ORM\JoinColumn(nullable: false)]
    private ?Quiz $quiz = null;


    #[ORM\Column(type: 'text')]
    #[Assert\NotBlank]
    private ?string $caseScenario = null;


    #[ORM\Column(type: 'text')]
    #[Assert\NotBlank]
    private ?string $questionText = null;


    #[ORM\OneToMany(mappedBy: "question", targetEntity: Option::class, cascade: ["persist"], orphanRemoval: true)]
    private Collection $options;


    public function __construct()
    {
        $this->options = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }


    public function getQuiz(): ?Quiz
    {
        return $this->quiz;
    }

    public function setQuiz($quiz): self
    {
        $this->quiz = $quiz;
        return $this;
    }

    public function getQuestionText(): ?string
    {
        return $this->questionText;
    }

    public function setQuestionText(string $questionText): self
    {
        $this->questionText = $questionText;

        return $this;
    }

    /**
     * @return Collection
     */
    public function getOptions(): Collection
    {
        return $this->options;
    }

    public function addOption(Option $option): self
    {
        if (!$this->options->contains($option)) {
            $this->options[] = $option;
            $option->setQuestion($this);
        }

        return $this;
    }

    public function removeOption(Option $option): self
    {
        if ($this->options->removeElement($option)) {
            if ($option->getQuestion() === $this) {
                $option->setQuestion(null);
            }
        }
        return $this;
    }

    //get option by id
    public function getOptionById(int $id): ?Option
    {
        foreach ($this->options as $option) {
            if ($option->getId() === $id) {
                return $option;
            }
        }
        return null;
    }

}