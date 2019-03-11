<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CrimeRepository")
 */
class Crime
{
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
     * @ORM\ManyToOne(targetEntity="App\Entity\Squadron")
     * @ORM\JoinColumn(nullable=false)
     */
    private $squadron;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\CrimeType")
     * @ORM\JoinColumn(nullable=false)
     */
    private $crime_type;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\MinorFaction")
     */
    private $minor_faction;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $victim;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $fine;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $bounty;

    /**
     * @ORM\Column(type="datetime")
     */
    private $committed_on;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $notes;

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

    public function getSquadron(): ?Squadron
    {
        return $this->squadron;
    }

    public function setSquadron(?Squadron $squadron): self
    {
        $this->squadron = $squadron;

        return $this;
    }

    public function getCrimeType(): ?CrimeType
    {
        return $this->crime_type;
    }

    public function setCrimeType(?CrimeType $crime_type): self
    {
        $this->crime_type = $crime_type;

        return $this;
    }

    public function getMinorFaction(): ?MinorFaction
    {
        return $this->minor_faction;
    }

    public function setMinorFaction(?MinorFaction $minor_faction): self
    {
        $this->minor_faction = $minor_faction;

        return $this;
    }

    public function getVictim(): ?string
    {
        return $this->victim;
    }

    public function setVictim(?string $victim): self
    {
        $this->victim = $victim;

        return $this;
    }

    public function getFine(): ?int
    {
        return $this->fine;
    }

    public function setFine(?int $fine): self
    {
        $this->fine = $fine;

        return $this;
    }

    public function getBounty(): ?int
    {
        return $this->bounty;
    }

    public function setBounty(?int $bounty): self
    {
        $this->bounty = $bounty;

        return $this;
    }

    public function getCommittedOn(): ?\DateTimeInterface
    {
        return $this->committed_on;
    }

    public function setCommittedOn(\DateTimeInterface $committed_on): self
    {
        $this->committed_on = $committed_on;

        return $this;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): self
    {
        $this->notes = $notes;

        return $this;
    }
}
