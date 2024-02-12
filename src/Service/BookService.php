<?php

namespace App\Service;

use App\DTO\BookDTO;
use App\Entity\Book;
use App\Repository\AuthorRepository;
use App\Repository\BookRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Exception\ValidatorException;
use Symfony\Component\Validator\Validator\ValidatorInterface;


class BookService
{
    private EntityManagerInterface $em;
    private ValidatorInterface $validator;

    private AuthorRepository $authorRepository;

    private BookRepository $bookRepository;
    public function __construct(
        EntityManagerInterface $em,
        ValidatorInterface $validator,
        AuthorRepository $authorRepository,
        BookRepository $bookRepository
    )
    {
        $this->em = $em;
        $this->validator = $validator;
        $this->authorRepository = $authorRepository;
        $this->bookRepository = $bookRepository;
    }



    public function create(BookDTO $dto): array
    {
        $book = new Book();
        $book->setTitle($dto->getTitle())
            ->setDescription($dto->getDescription())
            ->setCreatedAt($dto->getCreatedAt());

        foreach ($dto->getAuthors() as $author){
            $authorEntity = $this->authorRepository->find($author);
            if($authorEntity){
                $book->addAuthor($authorEntity);
            }
        }

        $this->em->persist($book);
        $this->em->flush();

        return [
            'id' => $book->getId(),
            'title' => $book->getTitle(),
            'description' => $book->getDescription(),
            'authors' => $this->_getAuthors($book),
            'createdAt' => $book->getCreatedAt(),
            'img' => $book->getImg()
        ];
    }

    public function update(int $id, BookDTO $dto): array
    {
        $book = $this->bookRepository->find($id);
        if($book){
            $book->setTitle($dto->getTitle())
                ->setDescription($dto->getDescription())
                ->setCreatedAt($dto->getCreatedAt());

            foreach ($dto->getAuthors() as $author){
                $authorEntity = $this->authorRepository->find($author);
                if($authorEntity){
                    $book->addAuthor($authorEntity);
                }
            }
        }

        $this->em->persist($book);
        $this->em->flush();



        return [
            'id' => $book->getId(),
            'title' => $book->getTitle(),
            'description' => $book->getDescription(),
            'authors' => $this->_getAuthors($book),
            'createdAt' => $book->getCreatedAt(),
            'img' => $book->getImg()
        ];
    }

    private function _getAuthors(Book $book): array
    {
        $authors = [];
        foreach ($book->getAuthors() as $author){
            $authors[] = [
                'id' => $author->getId(),
                'lastname' => $author->getLastname(),
                'firstname' => $author->getFirstname(),
                'secondname' => $author->getSecondname(),
            ];
        }

        return $authors;
    }
}