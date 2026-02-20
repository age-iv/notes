<?php

namespace App\DTO\Note;

use Symfony\Component\Validator\Constraints as Assert;

class CreateNoteRequest
{
    #[Assert\NotBlank(message: "Title cannot be blank")]
    #[Assert\Length(
        min: 3,
        max: 255,
        minMessage: "Title must be at least {{ limit }} characters",
        maxMessage: "Title must be no more than {{ limit }} characters"
    )]
    public string $title;

    #[Assert\NotBlank(message: "Content cannot be blank")]
    public string $content;

    public function __construct(string $title, string $content)
    {
        $this->title = $title;
        $this->content = $content;
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['title'] ?? '',
            $data['content'] ?? ''
        );
    }
}