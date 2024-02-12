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
    private FileUploaderService $fileUploaderService;
    private ValidatorInterface $validator;

    private AuthorRepository $authorRepository;

    private BookRepository $bookRepository;
    public function __construct(
        EntityManagerInterface $em,
        ValidatorInterface $validator,
        FileUploaderService $fileUploaderService,
        AuthorRepository $authorRepository,
        BookRepository $bookRepository
    )
    {
        $this->em = $em;
        $this->validator = $validator;
        $this->fileUploaderService = $fileUploaderService;
        $this->authorRepository = $authorRepository;
        $this->bookRepository = $bookRepository;
    }



    public function create(Request $request): array
    {
        $data = $request->getPayload()->all();

        $cat = (new \DateTimeImmutable($data['createdAt']));
        //dd($cat);
        $book = new Book();
        $book->setTitle($data['title'])
            ->setDescription($data['description'])
            ->setCreatedAt($cat);


        foreach ($data['authors'] as $author){
            $authorEntity = $this->authorRepository->find($author);
            if($authorEntity){
                $book->addAuthor($authorEntity);
            }
        }


        $errors = $this->validator->validate($book);
        if (count($errors) > 0){
            throw new ValidatorException($errors);
        }

        $img = $request->files->get('img');
        $imgName = $this->fileUploaderService->uploadImage($img);

        $book->setImg($imgName);

        $this->em->persist($book);
        $this->em->flush();

        return [
            'id' => $book->getId(),
            'title' => $book->getTitle(),
            'description' => $book->getDescription(),
            'authors' => $book->getAuthors(),
            'createdAt' => $book->getCreatedAt(),
            'img' => $book->getImg()
        ];
    }

    public function update(int $id, BookDTO $dto): array
    {
        //echo 111; die;
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



        /*$img = $request->files->get('img');
        $imgName = $this->fileUploaderService->uploadImage($img);

        $book->setImg($imgName);*/

        $this->em->persist($book);
        $this->em->flush();

        $authors = [];
        foreach ($book->getAuthors() as $author){
            $authors[] = [
                'lastname' => $author->getLastname(),
                'firstname' => $author->getFirstname(),
                'secondname' => $author->getSecondname(),
            ];
        }

        return [
            'id' => $book->getId(),
            'title' => $book->getTitle(),
            'description' => $book->getDescription(),
            'authors' => $authors,
            'createdAt' => $book->getCreatedAt(),
            'img' => $book->getImg()
        ];
    }
}