<?php

namespace App\Service;

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



    public function create(Request $request): array
    {
        $data = $request->getPayload()->all();
        #dd($data);
        $author = new Author();
        $author->setLastname($data['lastname'])
            ->setFirstname($data['firstname'])
            ->setSecondname($data['secondname'] ?? null);

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
            'secondname' => $author->getSecondname() ?? null,
        ];
    }
}