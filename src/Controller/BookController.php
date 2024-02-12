<?php

namespace App\Controller;

use App\DTO\BookDTO;
use App\Entity\Book;
use App\Repository\AuthorRepository;
use App\Repository\BookRepository;
use App\Service\BookService;
use App\Service\FileUploaderService;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Serializer\SerializerInterface;

class BookController extends AbstractController
{
    #[Route('/books', name: 'all_book',methods: ['GET'])]
    public function index(
        Request $request,
        BookRepository $bookRepository,
        PaginatorInterface $paginator,
        FileUploaderService $fileUploaderService
    ): JsonResponse
    {
        try {
            $data = $paginator->paginate(
                $bookRepository->findAll(),
                $request->query->getInt('page', 1),
                3
            );

            $items = $this->_getData($data, $fileUploaderService);

            return $this->json([
                'data' => $items,
                'currentPage' => $data->getCurrentPageNumber(),
                'last_page' => ceil($data->getTotalItemCount() / 3),
                'total_items' => $data->getTotalItemCount(),
                'limit' => 3,
            ]);
        } catch (\Exception $exception){
            return $this->json(['message' => $exception->getMessage(),], 400);
        }
    }

    #[Route('/books/create', name: 'create_book',methods: ['POST'])]
    public function create(
        Request $request,
        BookService $bookService,
        SerializerInterface $serializer,
        ValidatorInterface $validator,
    ): JsonResponse
    {
        try{
            $jsonContent = $request->getContent();
            $bookDto = $serializer->deserialize($jsonContent, BookDto::class, 'json');
            $errors = $validator->validate($bookDto);

            if (count($errors) > 0) {
                return $this->json(['errors' => $errors[0]->getMessage()], 400);
            }

            return $this->json($bookService->create($bookDto));
        } catch(\Exception $exception){
            return $this->json($exception->getMessage());
        }
    }

    #[Route('/books/author/{author}', name: 'author_book',methods: ['GET'])]
    public function showByAuthor(
        Request $request,
        AuthorRepository $authorRepository,
        PaginatorInterface $paginator,
        FileUploaderService $fileUploaderService,
        string $author
    ): JsonResponse
    {
        try {
            $author = $authorRepository->findOneBy(['lastname' => $author]);
            if (!$author){
                return $this->json(['message' => 'Author not found'], 404);
            }

            $data = $paginator->paginate(
                $author->getBooks(),
                $request->query->getInt('page', 1),
                3
            );

            $items = $this->_getData($data, $fileUploaderService);

            return $this->json([
                'data' => $items,
                'currentPage' => $data->getCurrentPageNumber(),
                'last_page' => ceil($data->getTotalItemCount() / 3),
                'total_items' => $data->getTotalItemCount(),
                'limit' => 3,
            ]);
        } catch (\Exception $exception){
            return $this->json(['message' => $exception->getMessage(),], 400);
        }
    }

    #[Route('/books/{id}', name: 'one_book',methods: ['GET'])]
    public function show(BookRepository $bookRepository, FileUploaderService $fileUploaderService, int $id): JsonResponse
    {
        try{
            $bookEntity = $bookRepository->find($id);
            if ($bookEntity){
                $authors = [];
                foreach ($bookEntity->getAuthors() as $author){
                    $authors[] = [
                        'id' => $author->getId(),
                        'lastname' => $author->getLastname(),
                        'firstname' => $author->getFirstname(),
                        'secondname' => $author->getSecondname(),
                    ];
                }
                $book = [
                    'id' => $bookEntity->getId(),
                    'title' => $bookEntity->getTitle(),
                    'description' => $bookEntity->getDescription(),
                    'createdAt' => $bookEntity->getCreatedAt(),
                    'img' => $bookEntity->getImg() ? $fileUploaderService->getImageFolder() . '/' . $bookEntity->getImg() : null,
                    'authors' => $authors
                ];
            } else {
                return $this->json(['message'=> 'Book not found'], 404);
            }

            return $this->json($book);
        } catch(\Exception $exception){
            return $this->json($exception->getMessage());
        }
    }

    #[Route('/books/update/{id}', name: 'update_book',methods: ['POST'])]
    public function update(
        Request $request,
        BookService $bookService,
        SerializerInterface $serializer,
        ValidatorInterface $validator,
        int $id): JsonResponse
    {
        try{
            $jsonContent = $request->getContent();
            $bookDto = $serializer->deserialize($jsonContent, BookDto::class, 'json');
            $errors = $validator->validate($bookDto);

            if (count($errors) > 0) {
                return $this->json(['errors' => $errors[0]->getMessage()], 400);
            }

            return $this->json($bookService->update($id, $bookDto));
        } catch(\Exception $exception){
            return $this->json($exception->getMessage());
        }
    }

    #[Route('/books/image/{id}', name: 'image_book',methods: ['POST'])]
    public function uploadImage(
        Request $request,
        FileUploaderService $fileUploaderService,
        BookRepository $bookRepository,
        EntityManagerInterface $em,
        int $id): JsonResponse
    {
        try{
            $book = $bookRepository->find($id);

            if (!$book){
                return $this->json(['message' => 'Book not found'], 404);
            }

            $img = $request->files->get('img');
            $imgName = $fileUploaderService->uploadImage($img);

            $oldImg = $book->getImg();
            if(!empty($oldImg) && file_exists($fileUploaderService->getImageFolder(). '/'. $oldImg)){
                unlink($fileUploaderService->getImageFolder(). '/'. $oldImg);
            }

            $book->setImg($imgName);

            $em->persist($book);
            $em->flush();

            return $this->json(['message' => 'Image was uploaded']);

        } catch(\Exception $exception){
            return $this->json(['message' => $exception->getMessage()], 400);
        }

    }

    private function _getData($data, FileUploaderService $fileUploaderService): array
    {
        $items = [];
        /** @var  Book $item */
        foreach ($data->getItems() as $item){
            $authors = [];
            foreach ($item->getAuthors() as $author){
                $authors[] = [
                    'id' => $author->getId(),
                    'lastname' => $author->getLastname(),
                    'firstname' => $author->getFirstname(),
                    'secondname' => $author->getSecondname(),
                ];
            }
            $items[] = [
                'id' => $item->getId(),
                'title' => $item->getTitle(),
                'description' => $item->getDescription(),
                'createdAt' => $item->getCreatedAt(),
                'img' => $item->getImg() ? $fileUploaderService->getImageFolder() . '/' . $item->getImg() : null,
                'authors' => $authors
            ];
        }

        return $items;
    }
}
