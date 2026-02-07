<?php

namespace App\Entity;

use App\Repository\NoteRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     description="Заметка",
 *     title="Note"
 * )
 */
#[ORM\Entity(repositoryClass: NoteRepository::class)]
class Note
{
    /**
     * @OA\Property(description="Уникальный идентификатор заметки", type="integer", example=1)
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * @OA\Property(description="Заголовок заметки", type="string", example="Моя заметка")
     */
    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Заголовок не может быть пустым")]
    #[Assert\Length(
        min: 3,
        max: 255,
        minMessage: "Заголовок должен быть не менее {{ limit }} символов",
        maxMessage: "Заголовок должен быть не более {{ limit }} символов"
    )]
    private ?string $title = null;

    /**
     * @OA\Property(description="Содержание заметки", type="string", example="Это содержание моей заметки")
     */
    #[ORM\Column(type: 'text')]
    #[Assert\NotBlank(message: "Содержание не может быть пустым")]
    private ?string $content = null;

    /**
     * @OA\Property(description="Дата создания", type="string", format="date-time", example="2023-10-01 12:00:00")
     */
    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    /**
     * @OA\Property(description="Дата последнего обновления", type="string", format="date-time", example="2023-10-01 12:00:00")
     */
    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): self
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }
}