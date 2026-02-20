<?php

namespace App\Tests\Controller\Api;

use App\Entity\Note;
use App\Repository\NoteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class NoteControllerTest extends WebTestCase
{
    private $client;
    private $entityManager;
    private $noteRepository;

    protected function setUp(): void
    {
        $this->client = static::createClient();

        $container = self::getContainer();
        $this->entityManager = $container->get(EntityManagerInterface::class);
        $this->noteRepository = $this->entityManager->getRepository(Note::class);

        $this->clearDatabase();
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

        $this->client->request('GET', '/api/notes/');

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Content-Type', 'application/json');

        $response = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('data', $response);
        $this->assertIsArray($response['data']);
        $this->assertCount(2, $response['data']);
    }

    public function testCreateNoteSuccess(): void
    {
        $data = [
            'title' => 'New Test Note',
            'content' => 'This is a test note content'
        ];

        $this->client->request(
            'POST',
            '/api/notes/',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);

        $response = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('data', $response);
        $this->assertArrayHasKey('title', $response['data']);
        $this->assertEquals('New Test Note', $response['data']['title']);
    }

    private function clearDatabase(): void
    {
        $notes = $this->noteRepository->findAll();
        foreach ($notes as $note) {
            $this->entityManager->remove($note);
        }
        $this->entityManager->flush();
    }
}