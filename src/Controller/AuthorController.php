<?php

namespace App\Controller;

use App\Repository\AuthorRepository;
use App\Service\AuthorService;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

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
    public function create(Request $request, AuthorService $authorService): JsonResponse
    {
        try {
            return $this->json($authorService->create($request));
        } catch (\Exception $exception){
            return $this->json(['message' => $exception->getMessage(),], 400);
        }

    }
}
