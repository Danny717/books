<?php

namespace App\DTO;

use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

class AuthorDTO
{

    #[Assert\Type('string')]
    #[Assert\NotBlank]
    #[Assert\Length(min: 3,minMessage: 'Your last name must be at least {{ limit }} characters long',)]
    public string $lastname;

    #[Assert\Type('string')]
    #[Assert\NotBlank]
    public string $firstname;

    #[Assert\Type('string')]
    public ?string $secondname;

    public function getLastname(): string
    {
        return $this->lastname;
    }

    public function getFirstname(): string
    {
        return $this->firstname;
    }

    public function getSecondname(): ?string
    {
        return $this->secondname ?? null;
    }




}
