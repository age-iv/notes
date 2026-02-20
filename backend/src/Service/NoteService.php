<?php

namespace App\Service;

use App\DTO\Note\CreateNoteRequest;
use App\DTO\Note\UpdateNoteRequest;
use App\DTO\Note\NoteResponse;
use App\Entity\Note;
use App\Repository\NoteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use App\DTO\Api\ErrorResponse;

class NoteService
{
    private NoteRepository $noteRepository;
    private EntityManagerInterface $entityManager;
    private ValidatorInterface $validator;

    public function __construct(
        NoteRepository $noteRepository,
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator
    ) {
        $this->noteRepository = $noteRepository;
        $this->entityManager = $entityManager;
        $this->validator = $validator;
    }

    /**
     * Получить все заметки
     *
     * @return NoteResponse[]
     */
    public function getAllNotes(): array
    {
        $notes = $this->noteRepository->findAll();

        return array_map(
            fn(Note $note) => NoteResponse::fromEntity($note),
            $notes
        );
    }

    /**
     * Получить заметку по ID
     */
    public function getNoteById(int $id): ?NoteResponse
    {
        $note = $this->noteRepository->find($id);

        if ($note === null) {
            return null;
        }

        return NoteResponse::fromEntity($note);
    }

    /**
     * Создать новую заметку
     *
     * @return array{success: bool, data?: NoteResponse, error?: ErrorResponse}
     */
    public function createNote(CreateNoteRequest $request): array
    {
        // Валидация DTO
        $errors = $this->validator->validate($request);

        if (count($errors) > 0) {
            $errorDetails = [];
            foreach ($errors as $error) {
                $errorDetails[] = [
                    'field' => $error->getPropertyPath(),
                    'message' => $error->getMessage(),
                ];
            }

            return [
                'success' => false,
                'error' => new ErrorResponse('Validation failed', $errorDetails),
            ];
        }

        // Создание сущности
        $note = new Note();
        $note->setTitle($request->title);
        $note->setContent($request->content);

        // Сохранение
        $this->entityManager->persist($note);
        $this->entityManager->flush();

        return [
            'success' => true,
            'data' => NoteResponse::fromEntity($note),
        ];
    }

    /**
     * Обновить заметку
     *
     * @return array{success: bool, data?: NoteResponse, error?: ErrorResponse}
     */
    public function updateNote(int $id, UpdateNoteRequest $request): array
    {
        // Найти заметку
        $note = $this->noteRepository->find($id);

        if ($note === null) {
            return [
                'success' => false,
                'error' => new ErrorResponse('Note not found'),
            ];
        }

        // Проверить есть ли изменения
        if (!$request->hasUpdates()) {
            return [
                'success' => false,
                'error' => new ErrorResponse('No data provided for update'),
            ];
        }

        // Валидация DTO
        $errors = $this->validator->validate($request);

        if (count($errors) > 0) {
            $errorDetails = [];
            foreach ($errors as $error) {
                $errorDetails[] = [
                    'field' => $error->getPropertyPath(),
                    'message' => $error->getMessage(),
                ];
            }

            return [
                'success' => false,
                'error' => new ErrorResponse('Validation failed', $errorDetails),
            ];
        }

        // Применить изменения
        if ($request->title !== null) {
            $note->setTitle($request->title);
        }

        if ($request->content !== null) {
            $note->setContent($request->content);
        }

        $note->updateTimestamp();

        // Сохранить
        $this->entityManager->flush();

        return [
            'success' => true,
            'data' => NoteResponse::fromEntity($note),
        ];
    }

    /**
     * Удалить заметку
     *
     * @return array{success: bool, error?: ErrorResponse}
     */
    public function deleteNote(int $id): array
    {
        $note = $this->noteRepository->find($id);

        if ($note === null) {
            return [
                'success' => false,
                'error' => new ErrorResponse('Note not found'),
            ];
        }

        $this->entityManager->remove($note);
        $this->entityManager->flush();

        return ['success' => true];
    }
}