<?php

namespace App\DTO;

use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

class BookDTO
{

    #[Assert\NotBlank]
    #[Assert\Type('string')]
    public string $title;

    #[Assert\Type('string')]
    public string $description;

    #[Assert\NotBlank]
    #[Assert\DateTime]
    public string $createdAt;

    #[Assert\All([
        new Assert\NotBlank,
        new Assert\Type('integer'),
    ])]
    public array $authors;

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return new \DateTimeImmutable($this->createdAt);
    }

    public function getAuthors(): array
    {
        return $this->authors;
    }
}
