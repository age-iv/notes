<?php

namespace App\Tests\DTO;

use App\DTO\Note\NoteResponse;
use App\Entity\Note;
use PHPUnit\Framework\TestCase;

class NoteResponseTest extends TestCase
{
    public function testFromEntity(): void
    {
        $note = new Note();
        $note->setTitle('Test Title');
        $note->setContent('Test Content');

        // Устанавливаем даты через рефлексию для теста
        $reflection = new \ReflectionClass($note);

        $createdAtProperty = $reflection->getProperty('created_at');
        $createdAtProperty->setAccessible(true);
        $createdAtProperty->setValue($note, new \DateTimeImmutable('2023-01-01 12:00:00'));

        $updatedAtProperty = $reflection->getProperty('updated_at');
        $updatedAtProperty->setAccessible(true);
        $updatedAtProperty->setValue($note, new \DateTimeImmutable('2023-01-02 12:00:00'));

        // Тестируем DTO
        $noteResponse = NoteResponse::fromEntity($note);

        $this->assertInstanceOf(NoteResponse::class, $noteResponse);
        $this->assertEquals('Test Title', $noteResponse->title);
        $this->assertEquals('Test Content', $noteResponse->content);
        $this->assertEquals('2023-01-01T12:00:00Z', $noteResponse->createdAt);
        $this->assertEquals('2023-01-02T12:00:00Z', $noteResponse->updatedAt);

        // Тестируем toArray
        $array = $noteResponse->toArray();
        $this->assertIsArray($array);
        $this->assertArrayHasKey('title', $array);
        $this->assertArrayHasKey('created_at', $array);
    }
}