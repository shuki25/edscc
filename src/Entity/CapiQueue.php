<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CapiQueueRepository")
 * @ORM\Table(indexes={@ORM\Index(name="progress_code_idx", columns={"progress_code"}),
 *     @ORM\Index(name="journal_date_idx", columns={"journal_date"})})
 */
class CapiQueue
{
    Use TimestampableEntity;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\Column(type="date")
     */
    private $journal_date;

    /**
     * @ORM\Column(type="string", length=1)
     */
    private $progress_code;

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

    public function getJournalDate(): ?\DateTimeInterface
    {
        return $this->journal_date;
    }

    public function setJournalDate(\DateTimeInterface $journal_date): self
    {
        $this->journal_date = $journal_date;

        return $this;
    }

    public function getProgressCode(): ?string
    {
        return $this->progress_code;
    }

    public function setProgressCode(string $progress_code): self
    {
        $this->progress_code = $progress_code;

        return $this;
    }
}
