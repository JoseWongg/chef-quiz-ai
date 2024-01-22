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

    /**
     * The constructor of the User class.
     */
    public function __construct() {
        $this->roles = [];
        #$this->reviews = new ArrayCollection();
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
}
