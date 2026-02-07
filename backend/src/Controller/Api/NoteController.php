<?php

namespace App\Controller\Api;

use App\Entity\Note;
use App\Repository\NoteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use OpenApi\Annotations as OA;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;

#[Route('/api/notes')]
class NoteController extends AbstractController
{
    /**
     * Получить все заметки
     *
     * @OA\Response(
     *     response=200,
     *     description="Возвращает список всех заметок",
     *     @OA\JsonContent(
     *         type="array",
     *         @OA\Items(ref=@Model(type=Note::class))
     *     )
     * )
     */
    #[Route('/', name: 'api_notes_list', methods: ['GET'])]
    public function index(NoteRepository $noteRepository): JsonResponse
    {
        $notes = $noteRepository->findAll();

        $notesArray = array_map(function (Note $note) {
            return [
                'id' => $note->getId(),
                'title' => $note->getTitle(),
                'content' => $note->getContent(),
                'createdAt' => $note->getCreatedAt()->format('Y-m-d H:i:s'),
                'updatedAt' => $note->getUpdatedAt()->format('Y-m-d H:i:s'),
            ];
        }, $notes);

        return $this->json($notesArray, Response::HTTP_OK);
    }

    /**
     * Получить конкретную заметку
     *
     * @OA\Parameter(
     *     name="id",
     *     in="path",
     *     description="ID заметки",
     *     required=true,
     *     @OA\Schema(type="integer")
     * )
     * @OA\Response(
     *     response=200,
     *     description="Возвращает заметку по ID",
     *     @OA\JsonContent(ref=@Model(type=Note::class))
     * )
     * @OA\Response(
     *     response=404,
     *     description="Заметка не найдена"
     * )
     */
    #[Route('/{id}', name: 'api_notes_show', methods: ['GET'])]
    public function show(Note $note): JsonResponse
    {
        $data = [
            'id' => $note->getId(),
            'title' => $note->getTitle(),
            'content' => $note->getContent(),
            'createdAt' => $note->getCreatedAt()->format('Y-m-d H:i:s'),
            'updatedAt' => $note->getUpdatedAt()->format('Y-m-d H:i:s'),
        ];

        return $this->json($data, Response::HTTP_OK);
    }

    /**
     * Создать новую заметку
     *
     * @OA\RequestBody(
     *     required=true,
     *     description="Данные для создания заметки",
     *     @OA\JsonContent(
     *         required={"title", "content"},
     *         @OA\Property(property="title", type="string", example="Моя заметка"),
     *         @OA\Property(property="content", type="string", example="Содержание заметки")
     *     )
     * )
     * @OA\Response(
     *     response=201,
     *     description="Заметка успешно создана",
     *     @OA\JsonContent(ref=@Model(type=Note::class))
     * )
     * @OA\Response(
     *     response=400,
     *     description="Некорректные данные"
     * )
     */
    #[Route('/', name: 'api_notes_create', methods: ['POST'])]
    public function create(
        Request $request,
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        $note = new Note();
        $note->setTitle($data['title'] ?? '');
        $note->setContent($data['content'] ?? '');

        $errors = $validator->validate($note);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }

            return $this->json(['errors' => $errorMessages], Response::HTTP_BAD_REQUEST);
        }

        $entityManager->persist($note);
        $entityManager->flush();

        return $this->json([
            'id' => $note->getId(),
            'title' => $note->getTitle(),
            'content' => $note->getContent(),
            'createdAt' => $note->getCreatedAt()->format('Y-m-d H:i:s'),
            'updatedAt' => $note->getUpdatedAt()->format('Y-m-d H:i:s'),
        ], Response::HTTP_CREATED);
    }

    /**
     * Обновить заметку
     *
     * @OA\Parameter(
     *     name="id",
     *     in="path",
     *     description="ID заметки",
     *     required=true,
     *     @OA\Schema(type="integer")
     * )
     * @OA\RequestBody(
     *     required=true,
     *     description="Данные для обновления заметки",
     *     @OA\JsonContent(
     *         @OA\Property(property="title", type="string", example="Обновленный заголовок"),
     *         @OA\Property(property="content", type="string", example="Обновленное содержание")
     *     )
     * )
     * @OA\Response(
     *     response=200,
     *     description="Заметка успешно обновлена",
     *     @OA\JsonContent(ref=@Model(type=Note::class))
     * )
     * @OA\Response(
     *     response=400,
     *     description="Некорректные данные"
     * )
     * @OA\Response(
     *     response=404,
     *     description="Заметка не найдена"
     * )
     */
    #[Route('/{id}', name: 'api_notes_update', methods: ['PUT'])]
    public function update(
        Note $note,
        Request $request,
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        $note->setTitle($data['title'] ?? $note->getTitle());
        $note->setContent($data['content'] ?? $note->getContent());
        $note->setUpdatedAt(new \DateTimeImmutable());

        $errors = $validator->validate($note);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }

            return $this->json(['errors' => $errorMessages], Response::HTTP_BAD_REQUEST);
        }

        $entityManager->flush();

        return $this->json([
            'id' => $note->getId(),
            'title' => $note->getTitle(),
            'content' => $note->getContent(),
            'createdAt' => $note->getCreatedAt()->format('Y-m-d H:i:s'),
            'updatedAt' => $note->getUpdatedAt()->format('Y-m-d H:i:s'),
        ], Response::HTTP_OK);
    }

    /**
     * Удалить заметку
     *
     * @OA\Parameter(
     *     name="id",
     *     in="path",
     *     description="ID заметки",
     *     required=true,
     *     @OA\Schema(type="integer")
     * )
     * @OA\Response(
     *     response=204,
     *     description="Заметка успешно удалена"
     * )
     * @OA\Response(
     *     response=404,
     *     description="Заметка не найдена"
     * )
     */
    #[Route('/{id}', name: 'api_notes_delete', methods: ['DELETE'])]
    public function delete(Note $note, EntityManagerInterface $entityManager): JsonResponse
    {
        $entityManager->remove($note);
        $entityManager->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}