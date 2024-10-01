<?php

namespace App\Entity;

use App\Repository\ProcessedUrlRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ProcessedUrlRepository::class)
 */
class ProcessedUrl
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="datetime_immutable")
     */
    private $processedDate;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $url;

    /**
     * @ORM\Column(type="datetime_immutable")
     */
    private $createdDate;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProcessedDate(): ?\DateTimeImmutable
    {
        return $this->processedDate;
    }

    public function setProcessedDate(\DateTimeImmutable $processedDate): self
    {
        $this->processedDate = $processedDate;

        return $this;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(string $url): self
    {
        $this->url = $url;

        return $this;
    }

    public function getCreatedDate(): ?\DateTimeImmutable
    {
        return $this->createdDate;
    }

    public function setCreatedDate(\DateTimeImmutable $createdDate): self
    {
        $this->createdDate = $createdDate;

        return $this;
    }
}
