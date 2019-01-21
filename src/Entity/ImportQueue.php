<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ImportQueueRepository")
 */
class ImportQueue
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="importQueues")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $original_filename;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $upload_filename;

    /**
     * @ORM\Column(type="datetime")
     */
    private $game_datetime;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $time_started;

    /**
     * @ORM\Column(type="string", length=1, nullable=true)
     */
    private $progress_code;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $progress_percent = 0;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getUploadFilename(): ?string
    {
        return $this->upload_filename;
    }

    public function setUploadFilename(string $upload_filename): self
    {
        $this->upload_filename = $upload_filename;

        return $this;
    }

    public function getTimeStarted(): ?\DateTimeInterface
    {
        return $this->time_started;
    }

    public function setTimeStarted(?\DateTimeInterface $time_started = null): self
    {
        $this->time_started = isset($time_started) ? $time_started : new \DateTime('now');
        return $this;
    }

    public function getProgressCode(): ?string
    {
        return $this->progress_code;
    }

    public function setProgressCode(?string $progress_code): self
    {
        $this->progress_code = $progress_code;

        return $this;
    }

    public function getProgressPercent(): ?float
    {
        return $this->progress_percent;
    }

    public function setProgressPercent(?float $progress_percent): self
    {
        $this->progress_percent = $progress_percent;

        return $this;
    }

    public function getOriginalFilename(): ?string
    {
        return $this->original_filename;
    }

    public function setOriginalFilename(?string $original_filename): self
    {
        $this->original_filename = $original_filename;

        return $this;
    }

    public function getGameDatetime(): ?\DateTimeInterface
    {
        return $this->game_datetime;
    }

    public function setGameDatetime(\DateTimeInterface $game_datetime): self
    {
        $this->game_datetime = $game_datetime;

        return $this;
    }
}
