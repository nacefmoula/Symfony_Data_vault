<?php

namespace App\Entity;

use App\Repository\DocumentRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DocumentRepository::class)]
class Document
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'documents')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(length: 255)]
    private ?string $filePath = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $sharedToken = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $sharedTokenExpiresAt = null;

    #[ORM\Column(nullable: true)]
    private ?int $sharedTokenMaxUses = null;

    #[ORM\Column(nullable: true)]
    private ?int $sharedTokenUses = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(string $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getFilePath(): ?string
    {
        return $this->filePath;
    }

    public function setFilePath(string $filePath): static
    {
        $this->filePath = $filePath;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getSharedToken(): ?string
    {
        return $this->sharedToken;
    }

    public function setSharedToken(?string $sharedToken): static
    {
        $this->sharedToken = $sharedToken;

        return $this;
    }

    public function getSharedTokenExpiresAt(): ?\DateTimeImmutable
    {
        return $this->sharedTokenExpiresAt;
    }

    public function setSharedTokenExpiresAt(?\DateTimeImmutable $sharedTokenExpiresAt): static
    {
        $this->sharedTokenExpiresAt = $sharedTokenExpiresAt;

        return $this;
    }

    public function getSharedTokenMaxUses(): ?int
    {
        return $this->sharedTokenMaxUses;
    }

    public function setSharedTokenMaxUses(?int $sharedTokenMaxUses): static
    {
        $this->sharedTokenMaxUses = $sharedTokenMaxUses;
        return $this;
    }

    public function getSharedTokenUses(): ?int
    {
        return $this->sharedTokenUses;
    }

    public function setSharedTokenUses(?int $sharedTokenUses): static
    {
        $this->sharedTokenUses = $sharedTokenUses;
        return $this;
    }
}
