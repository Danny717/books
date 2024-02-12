<?php

namespace App\Service;

use App\DTO\AuthorDTO;
use App\Entity\Author;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Exception\ValidatorException;
use Symfony\Component\Validator\Validator\ValidatorInterface;


class AuthorService
{
    private EntityManagerInterface $em;
    private ValidatorInterface $validator;
    public function __construct(
        EntityManagerInterface $em,
        ValidatorInterface $validator
    )
    {
        $this->em = $em;
        $this->validator = $validator;
    }



    public function create(AuthorDTO$dto): array
    {
        $author = new Author();
        $author->setLastname($dto->getLastname())
            ->setFirstname($dto->getFirstname())
            ->setSecondname($dto->getSecondname() ?? null);

        $errors = $this->validator->validate($author);
        if (count($errors) > 0){
            throw new ValidatorException($errors);
        }

        $this->em->persist($author);
        $this->em->flush();

        return [
            'id' => $author->getId(),
            'firstname' => $author->getFirstname(),
            'lastname' => $author->getLastname(),
            'secondname' => $author->getSecondname(),
        ];
    }
}