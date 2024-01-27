<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * This class represents a logged-in User of the application.
 */
#[ORM\Entity(repositoryClass: UserRepository::class)]
#[UniqueEntity(
    fields: ['email'],
    message: 'There is already an account with this email.'
)]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    #[Assert\NotBlank]
    #[Assert\Email(
        message: 'The email "{{ value }}" is not a valid email.',
        mode: 'strict'
    )]
    private ?string $email = null;

    #[ORM\Column]
    private array $roles = [];

    #[Assert\Length(
        min: 8,
        max: 4096,
        minMessage: 'Your password must be at least {{ limit }} characters long',
    )]
    private ?string $plainPassword = null;

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(type: "string", length: 255)]
    #[Assert\NotBlank (message: 'Please enter your name.') ]
    private ?string $name = null;


    //Collection of Quiz entities created by the user
    #[ORM\OneToMany(mappedBy: "trainer", targetEntity: Quiz::class)]
    private Collection $createdQuizzes;

    // Collection of AssignedQuiz entities assigned by the user (trainer)
    #[ORM\OneToMany(mappedBy: "assigner", targetEntity: AssignedQuiz::class)]
    private Collection $assignedQuizzes;

    // Collection of AssignedQuiz entities assigned to the user (chef)
    #[ORM\OneToMany(mappedBy: "chef", targetEntity: AssignedQuiz::class)]
    private Collection $receivedQuizzes;

    /**
     * The constructor of the User class.
     */
    public function __construct() {
        $this->roles = [];
        $this->createdQuizzes = new ArrayCollection();
        $this->assignedQuizzes = new ArrayCollection();
        $this->receivedQuizzes = new ArrayCollection();
    }

    /**
     *
     * @return string
     * Returns the id of the user.
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     *
     * @return string
     * Returns the name of the user.
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     *
     * @param string $email
     * Sets the email of the user.
     */
    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @return array
     * Returns the roles of the user.
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param array $roles
     * @return $this
     * This method sets the roles of the user.
     */
    public function setRoles(array $roles): static
    {
        // Remove duplicate values and reset the roles
        $this->roles = array_unique($roles);

        return $this;
    }

    /**
     *
     * @return string|null
     * Returns the plain password of the user.
     */
    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }


    /**
     * @param string|null $plainPassword
     * The password to be hashed. It is not persisted in the database.
     */
    public function setPlainPassword(?string $plainPassword): void
    {
        $this->plainPassword = $plainPassword;
    }


    /**
     * @return string
     * Returns the password of the user.
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     *
     * @param string $password
     * Sets the password of the user.
     */
    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     *
     * @return string|null
     * Returns the name of the user.
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     *
     * @param string|null $name
     * Sets the name of the user.
     */
    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        $this->plainPassword = null;
    }

    public function getCreatedQuizzes(): Collection
    {
        return $this->createdQuizzes;
    }

    public function addCreatedQuiz(Quiz $quiz): self
    {
        if (!$this->createdQuizzes->contains($quiz)) {
            $this->createdQuizzes[] = $quiz;
            $quiz->setTrainer($this);
        }

        return $this;
    }

    public function removeCreatedQuiz(Quiz $quiz): self
    {
        if ($this->createdQuizzes->removeElement($quiz)) {
            if ($quiz->getTrainer() === $this) {
                $quiz->setTrainer(null);
            }
        }

        return $this;
    }

    public function getAssignedQuizzes(): Collection
    {
        return $this->assignedQuizzes;
    }

    public function addAssignedQuiz(AssignedQuiz $assignedQuiz): self
    {
        if (!$this->assignedQuizzes->contains($assignedQuiz)) {
            $this->assignedQuizzes[] = $assignedQuiz;
            $assignedQuiz->setAssigner($this);
        }

        return $this;
    }

    public function removeAssignedQuiz(AssignedQuiz $assignedQuiz): self
    {
        if ($this->assignedQuizzes->removeElement($assignedQuiz)) {
            // set the owning side to null (unless already changed)
            if ($assignedQuiz->getChef() === $this) {
                $assignedQuiz->setChef(null);
            }
        }

        return $this;
    }

    public function createQuiz(string $title, string $caseScenario): Quiz
    {
        $quiz = new Quiz();
        $quiz->setTitle($title);
        $quiz->setCaseScenario($caseScenario);
        $quiz->setCreationDate(new \DateTime());
        $this->addCreatedQuiz($quiz);

        return $quiz;
    }

    public function assignQuizToChef(AssignedQuiz $assignedQuiz, User $chef): void
    {
        // Set the trainer (assigner) of the AssignedQuiz
        $assignedQuiz->setAssigner($this);
        $this->addAssignedQuiz($assignedQuiz);

        // Set the chef (receiver) of the AssignedQuiz and add it to the chef's receivedQuizzes
        $chef->addReceivedQuiz($assignedQuiz);
    }


    public function addReceivedQuiz(AssignedQuiz $assignedQuiz): self
    {
        if (!$this->receivedQuizzes->contains($assignedQuiz)) {
            $this->receivedQuizzes[] = $assignedQuiz;
            $assignedQuiz->setChef($this); // Set the chef of the AssignedQuiz
        }

        return $this;
    }
}
