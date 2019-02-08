<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\EdmcRepository")
 */
class Edmc
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="edmcs")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $entry;

    /**
     * @ORM\Column(type="datetime")
     */
    private $enteredAt;

    /**
     * @ORM\Column(type="boolean", options={"default": "0"})
     */
    private $processed_flag = 0;

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

    public function getEnteredAt(): ?\DateTimeInterface
    {
        return $this->enteredAt;
    }

    public function setEnteredAt(\DateTimeInterface $enteredAt): self
    {
        $this->enteredAt = $enteredAt;

        return $this;
    }

    public function getProcessedFlag(): ?bool
    {
        return $this->processed_flag;
    }

    public function setProcessedFlag(bool $processed_flag): self
    {
        $this->processed_flag = $processed_flag;

        return $this;
    }

    public function getEntry()
    {
        return $this->entry;
    }

    public function setEntry($entry): self
    {
        $this->entry = $entry;

        return $this;
    }
}
