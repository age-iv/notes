<?php

namespace App\DTO\Note;

use App\Entity\Note;

#[OA\Schema(
    title: 'NoteResponse',
    description: 'The representation of a Note resource.'
)]
class NoteResponse
{
    #[OA\Property(description: 'The unique identifier of the note.', type: 'integer', format: 'int64', example: 42)]
    public int $id;

    #[OA\Property(description: 'The title of the note.', type: 'string', example: 'My Important Meeting Notes')]
    public string $title;

    #[OA\Property(description: 'The content of the note.', type: 'string', example: 'Discussed project timelines...')]
    public string $content;

    #[OA\Property(description: 'ISO 8601 timestamp of when the note was created.', type: 'string', format: 'date-time', example: '2023-10-01T12:00:00Z')]
    public string $createdAt;

    #[OA\Property(description: 'ISO 8601 timestamp of when the note was last updated.', type: 'string', format: 'date-time', example: '2023-10-02T15:30:00Z')]
    public string $updatedAt;

    public function __construct(
        int $id,
        string $title,
        string $content,
        string $createdAt,
        string $updatedAt
    ) {
        $this->id = $id;
        $this->title = $title;
        $this->content = $content;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }

    public static function fromEntity(Note $note): self
    {
        return new self(
            $note->getId(),
            $note->getTitle(),
            $note->getContent(),
            $note->getCreatedAt()->format('Y-m-d\TH:i:s\Z'),
            $note->getUpdatedAt()->format('Y-m-d\TH:i:s\Z')
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'content' => $this->content,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
}