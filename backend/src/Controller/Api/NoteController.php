<?php

namespace App\Controller\Api;

use App\DTO\Note\CreateNoteRequest;
use App\DTO\Note\UpdateNoteRequest;
use App\Service\NoteService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/notes')]
#[OA\Tag(name: 'Notes', description: 'Operations for managing notes')]
class NoteController extends AbstractController
{
    private NoteService $noteService;

    public function __construct(NoteService $noteService)
    {
        $this->noteService = $noteService;
    }

    #[Route('/', name: 'api_notes_list', methods: ['GET'])]
    #[OA\Get(
        summary: 'Get all notes',
        description: 'Retrieves a list of all notes, sorted by creation date (newest first).',
        responses: [
            new OA\Response(
                response: Response::HTTP_OK,
                description: 'Successful operation',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(ref: new Model(type: NoteResponse::class))
                )
            )
        ]
    )]
    public function index(): JsonResponse
    {
        $notes = $this->noteService->getAllNotes();
        $data = array_map(fn($note) => $note->toArray(), $notes);

        return $this->json(['data' => $data], Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'api_notes_show', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        $note = $this->noteService->getNoteById($id);

        if ($note === null) {
            return $this->json(
                ['error' => 'Note not found'],
                Response::HTTP_NOT_FOUND
            );
        }

        return $this->json(['data' => $note->toArray()], Response::HTTP_OK);
    }

    #[Route('/', name: 'api_notes_create', methods: ['POST'])]
    #[OA\Post(
        summary: 'Create a new note',
        description: 'Creates a new note after validating the provided title and content.',
        requestBody: new OA\RequestBody(
            description: 'Note data to create',
            required: true,
            content: new OA\JsonContent(ref: new Model(type: CreateNoteRequest::class))
        ),
        responses: [
            new OA\Response(
                response: Response::HTTP_CREATED,
                description: 'Note successfully created',
                content: new OA\JsonContent(ref: new Model(type: NoteResponse::class))
            ),
            new OA\Response(
                response: Response::HTTP_BAD_REQUEST,
                description: 'Validation error. Check the error details.',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'error', type: 'string'),
                        new OA\Property(property: 'details', type: 'array', items: new OA\Items(type: 'string')),
                        new OA\Property(property: 'timestamp', type: 'string', format: 'date-time')
                    ]
                )
            )
        ]
    )]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if ($data === null) {
            return $this->json(
                ['error' => 'Invalid JSON data'],
                Response::HTTP_BAD_REQUEST
            );
        }

        $createRequest = CreateNoteRequest::fromArray($data);
        $result = $this->noteService->createNote($createRequest);

        if (!$result['success']) {
            return $this->json(
                $result['error']->toArray(),
                Response::HTTP_BAD_REQUEST
            );
        }

        return $this->json(
            ['data' => $result['data']->toArray()],
            Response::HTTP_CREATED
        );
    }

    #[Route('/{id}', name: 'api_notes_update', methods: ['PUT'])]
    public function update(int $id, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if ($data === null) {
            return $this->json(
                ['error' => 'Invalid JSON data'],
                Response::HTTP_BAD_REQUEST
            );
        }

        $updateRequest = UpdateNoteRequest::fromArray($data);
        $result = $this->noteService->updateNote($id, $updateRequest);

        if (!$result['success']) {
            $statusCode = $result['error']->error === 'Note not found'
                ? Response::HTTP_NOT_FOUND
                : Response::HTTP_BAD_REQUEST;

            return $this->json(
                $result['error']->toArray(),
                $statusCode
            );
        }

        return $this->json(
            ['data' => $result['data']->toArray()],
            Response::HTTP_OK
        );
    }

    #[Route('/{id}', name: 'api_notes_delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $result = $this->noteService->deleteNote($id);

        if (!$result['success']) {
            return $this->json(
                $result['error']->toArray(),
                Response::HTTP_NOT_FOUND
            );
        }

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}