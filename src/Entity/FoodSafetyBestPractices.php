<?php

namespace App\Entity;

use App\Repository\FoodSafetyBestPracticesRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use InvalidArgumentException;

#[ORM\Entity(repositoryClass: FoodSafetyBestPracticesRepository::class)]
class FoodSafetyBestPractices
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $rule = null;

    #[ORM\Column(length: 50)]
    private ?string $topic = null;

    private const ALLOWED_TOPICS = [
        'Cleaning',
        'Chilling Food',
        'Cooking Food',
        'Cross-contamination',
        'Allergens',
        'Initial Food Safety Training',
    ];

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRule(): ?string
    {
        return $this->rule;
    }

    public function setRule(string $rule): static
    {
        $this->rule = $rule;

        return $this;
    }

    public function getTopic(): ?string
    {
        return $this->topic;
    }

    public function setTopic(string $topic): self
    {
        if (!in_array($topic, self::ALLOWED_TOPICS, true)) {
            throw new InvalidArgumentException("Invalid topic: $topic");
        }
        $this->topic = $topic;

        return $this;
    }
}
