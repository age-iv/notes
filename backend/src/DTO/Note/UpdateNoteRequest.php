<?php

namespace App\DTO\Note;

use Symfony\Component\Validator\Constraints as Assert;

class UpdateNoteRequest
{
    #[Assert\Length(
        min: 3,
        max: 255,
        minMessage: "Title must be at least {{ limit }} characters",
        maxMessage: "Title must be no more than {{ limit }} characters"
    )]
    public ?string $title = null;

    #[Assert\NotBlank(message: "Content cannot be blank", allowNull: true)]
    public ?string $content = null;

    public function __construct(?string $title = null, ?string $content = null)
    {
        $this->title = $title;
        $this->content = $content;
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['title'] ?? null,
            $data['content'] ?? null
        );
    }

    public function hasUpdates(): bool
    {
        return $this->title !== null || $this->content !== null;
    }
}