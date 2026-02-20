<?php

namespace App\Tests\Service;

use App\DTO\Note\CreateNoteRequest;
use App\DTO\Note\UpdateNoteRequest;
use App\Entity\Note;
use App\Repository\NoteRepository;
use App\Service\NoteService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class NoteServiceTest extends KernelTestCase
{
    private NoteService $noteService;
    private EntityManagerInterface $entityManager;
    private NoteRepository $noteRepository;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();

        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();

        $this->noteRepository = $this->entityManager->getRepository(Note::class);

        $validator = $kernel->getContainer()->get(ValidatorInterface::class);

        $this->noteService = new NoteService(
            $this->noteRepository,
            $this->entityManager,
            $validator
        );

        $this->clearDatabase();
    }

    public function testCreateNoteSuccess(): void
    {
        $request = new CreateNoteRequest('Valid Title', 'Valid Content');

        $result = $this->noteService->createNote($request);

        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('data', $result);
        $this->assertEquals('Valid Title', $result['data']->title);

        // Проверяем что сохранено в БД
        $notes = $this->noteRepository->findAll();
        $this->assertCount(1, $notes);
        $this->assertEquals('Valid Title', $notes[0]->getTitle());
    }

    public function testCreateNoteValidationError(): void
    {
        $request = new CreateNoteRequest('', 'Content'); // Пустой заголовок

        $result = $this->noteService->createNote($request);

        $this->assertFalse($result['success']);
        $this->assertArrayHasKey('error', $result);
        $this->assertEquals('Validation failed', $result['error']->error);
    }

    public function testGetAllNotes(): void
    {
        // Создаем тестовые заметки напрямую
        $note1 = new Note();
        $note1->setTitle('Note 1');
        $note1->setContent('Content 1');

        $note2 = new Note();
        $note2->setTitle('Note 2');
        $note2->setContent('Content 2');

        $this->entityManager->persist($note1);
        $this->entityManager->persist($note2);
        $this->entityManager->flush();

        $notes = $this->noteService->getAllNotes();

        $this->assertCount(2, $notes);
        $this->assertEquals('Note 1', $notes[0]->title);
        $this->assertEquals('Note 2', $notes[1]->title);
    }

    public function testGetNoteById(): void
    {
        $note = new Note();
        $note->setTitle('Test Note');
        $note->setContent('Test Content');

        $this->entityManager->persist($note);
        $this->entityManager->flush();

        $result = $this->noteService->getNoteById($note->getId());

        $this->assertNotNull($result);
        $this->assertEquals('Test Note', $result->title);

        // Несуществующий ID
        $result = $this->noteService->getNoteById(999999);
        $this->assertNull($result);
    }

    public function testUpdateNoteSuccess(): void
    {
        $note = new Note();
        $note->setTitle('Original Title');
        $note->setContent('Original Content');

        $this->entityManager->persist($note);
        $this->entityManager->flush();

        $request = new UpdateNoteRequest('Updated Title', 'Updated Content');
        $result = $this->noteService->updateNote($note->getId(), $request);

        $this->assertTrue($result['success']);
        $this->assertEquals('Updated Title', $result['data']->title);

        // Проверяем в БД
        $updatedNote = $this->noteRepository->find($note->getId());
        $this->assertEquals('Updated Title', $updatedNote->getTitle());
    }

    public function testDeleteNoteSuccess(): void
    {
        $note = new Note();
        $note->setTitle('To Delete');
        $note->setContent('Will be deleted');

        $this->entityManager->persist($note);
        $this->entityManager->flush();
        $noteId = $note->getId();

        $result = $this->noteService->deleteNote($noteId);

        $this->assertTrue($result['success']);

        $deletedNote = $this->noteRepository->find($noteId);
        $this->assertNull($deletedNote);
    }

    private function clearDatabase(): void
    {
        $notes = $this->noteRepository->findAll();
        foreach ($notes as $note) {
            $this->entityManager->remove($note);
        }
        $this->entityManager->flush();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->entityManager->close();
    }
}