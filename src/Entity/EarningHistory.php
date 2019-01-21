<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\EarningHistoryRepository")
 * @ORM\Table(indexes={@ORM\Index(name="earned_on_idx", columns={"earned_on"})})
 */
class EarningHistory
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
     * @ORM\Column(type="date")
     */
    private $earned_on;

    /**
     * @ORM\Column(type="integer")
     */
    private $reward;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\EarningType")
     * @ORM\JoinColumn(nullable=false)
     */
    private $earning_type;

    /**
     * @ORM\Column(type="integer")
     */
    private $crew_wage;

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

    public function getEarnedOn(): ?\DateTimeInterface
    {
        return $this->earned_on;
    }

    public function setEarnedOn(\DateTimeInterface $earned_on): self
    {
        $this->earned_on = $earned_on;

        return $this;
    }

    public function getReward(): ?int
    {
        return $this->reward;
    }

    public function setReward(int $reward): self
    {
        $this->reward = $reward;

        return $this;
    }

    public function getEarningType(): ?EarningType
    {
        return $this->earning_type;
    }

    public function setEarningType(?EarningType $earning_type): self
    {
        $this->earning_type = $earning_type;

        return $this;
    }

    public function getCrewWage(): ?int
    {
        return $this->crew_wage;
    }

    public function setCrewWage(int $crew_wage): self
    {
        $this->crew_wage = $crew_wage;

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
