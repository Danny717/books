<?php

namespace App\Controller;

use App\DTO\AuthorDTO;
use App\DTO\BookDTO;
use App\Repository\AuthorRepository;
use App\Service\AuthorService;
use App\Service\BookService;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class AuthorController extends AbstractController
{
    #[Route('/authors', name: 'all_authors', methods: ['GET'],)]
    public function index(Request $request, AuthorRepository $authorRepository, PaginatorInterface $paginator): JsonResponse
    {
        try {
            $data = $paginator->paginate(
                $authorRepository->findAll(),
                $request->query->getInt('page', 1),
                3
            );

            $items = [];
            foreach ($data->getItems() as $item){
                $items[] = [
                    'id' => $item->getId(),
                    'firstname' => $item->getFirstname(),
                    'lastname' => $item->getLastname(),
                    'secondname' => $item->getSecondname(),
                ];
            }

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

    #[Route('/authors/create', name: 'create_author', methods: ['POST'],)]
    public function create(
        Request $request,
        AuthorService $authorService,
        SerializerInterface $serializer,
        ValidatorInterface $validator,
    ): JsonResponse
    {
        try {
            $jsonContent = $request->getContent();
            $authorDto = $serializer->deserialize($jsonContent, AuthorDTO::class, 'json');
            $errors = $validator->validate($authorDto);

            if (count($errors) > 0) {
                return $this->json(['errors' => $errors[0]->getMessage()], 400);
            }

            return $this->json($authorService->create($authorDto));
        } catch (\Exception $exception){
            return $this->json(['message' => $exception->getMessage(),], 400);
        }

    }
}
