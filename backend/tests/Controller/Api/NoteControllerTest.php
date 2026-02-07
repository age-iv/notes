<?php

namespace App\Tests\Controller\Api;

use App\Entity\Note;
use App\Repository\NoteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class NoteControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $entityManager;
    private NoteRepository $noteRepository;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $this->noteRepository = $this->entityManager->getRepository(Note::class);
        
        // Clear database before each test
        foreach ($this->noteRepository->findAll() as $note) {
            $this->entityManager->remove($note);
        }
        $this->entityManager->flush();
    }

    public function testGetAllNotes(): void
    {
        // Create test notes
        $note1 = (new Note())
            ->setTitle('Test Note 1')
            ->setContent('Content 1');
        
        $note2 = (new Note())
            ->setTitle('Test Note 2')
            ->setContent('Content 2');
        
        $this->entityManager->persist($note1);
        $this->entityManager->persist($note2);
        $this->entityManager->flush();

        $this->client->request('GET', '/api/notes');
        
        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Content-Type', 'application/json');
        
        $response = json_decode($this->client->getResponse()->getContent(), true);
        
        $this->assertIsArray($response);
        $this->assertCount(2, $response);
        $this->assertEquals('Test Note 1', $response[0]['title']);
        $this->assertEquals('Test Note 2', $response[1]['title']);
    }

    public function testCreateNote(): void
    {
        $data = [
            'title' => 'New Test Note',
            'content' => 'This is a test note content'
        ];

        $this->client->request(
            'POST',
            '/api/notes',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );

        $this->assertResponseStatusCodeSame(201);
        
        $response = json_decode($this->client->getResponse()->getContent(), true);
        
        $this->assertArrayHasKey('id', $response);
        $this->assertEquals('New Test Note', $response['title']);
        $this->assertEquals('This is a test note content', $response['content']);
        
        // Verify note was saved in database
        $note = $this->noteRepository->find($response['id']);
        $this->assertNotNull($note);
        $this->assertEquals('New Test Note', $note->getTitle());
    }

    public function testCreateNoteWithInvalidData(): void
    {
        $data = [
            'title' => '', // Invalid: empty title
            'content' => 'Content'
        ];

        $this->client->request(
            'POST',
            '/api/notes',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );

        $this->assertResponseStatusCodeSame(400);
        
        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('errors', $response);
        $this->assertArrayHasKey('title', $response['errors']);
    }

    public function testShowNote(): void
    {
        $note = (new Note())
            ->setTitle('Show Test Note')
            ->setContent('Content for show test');
        
        $this->entityManager->persist($note);
        $this->entityManager->flush();

        $this->client->request('GET', '/api/notes/' . $note->getId());
        
        $this->assertResponseIsSuccessful();
        
        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('Show Test Note', $response['title']);
        $this->assertEquals('Content for show test', $response['content']);
    }

    public function testUpdateNote(): void
    {
        $note = (new Note())
            ->setTitle('Original Title')
            ->setContent('Original Content');
        
        $this->entityManager->persist($note);
        $this->entityManager->flush();

        $updateData = [
            'title' => 'Updated Title',
            'content' => 'Updated Content'
        ];

        $this->client->request(
            'PUT',
            '/api/notes/' . $note->getId(),
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($updateData)
        );

        $this->assertResponseIsSuccessful();
        
        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('Updated Title', $response['title']);
        $this->assertEquals('Updated Content', $response['content']);
        
        // Verify update in database
        $updatedNote = $this->noteRepository->find($note->getId());
        $this->assertEquals('Updated Title', $updatedNote->getTitle());
    }

    public function testDeleteNote(): void
    {
        $note = (new Note())
            ->setTitle('Note to Delete')
            ->setContent('This note will be deleted');
        
        $this->entityManager->persist($note);
        $this->entityManager->flush();
        $noteId = $note->getId();

        $this->client->request('DELETE', '/api/notes/' . $noteId);
        
        $this->assertResponseStatusCodeSame(204);
        
        // Verify deletion from database
        $deletedNote = $this->noteRepository->find($noteId);
        $this->assertNull($deletedNote);
    }

    public function testShowNonExistentNote(): void
    {
        $this->client->request('GET', '/api/notes/999999');
        
        $this->assertResponseStatusCodeSame(404);
    }
}
