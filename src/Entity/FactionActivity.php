<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\FactionActivityRepository")
 * @ORM\Table(indexes={@ORM\Index(name="earned_on_idx", columns={"earned_on"})})
 */
class FactionActivity
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
     * @ORM\ManyToOne(targetEntity="App\Entity\EarningType")
     * @ORM\JoinColumn(nullable=false)
     */
    private $earning_type;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\MinorFaction")
     */
    private $minor_faction;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\MinorFaction")
     */
    private $target_minor_faction;

    /**
     * @ORM\Column(type="date")
     */
    private $earned_on;

    /**
     * @ORM\Column(type="integer")
     */
    private $reward;

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

    public function getEarningType(): ?EarningType
    {
        return $this->earning_type;
    }

    public function setEarningType(?EarningType $earning_type): self
    {
        $this->earning_type = $earning_type;

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

    public function getTargetMinorFaction(): ?MinorFaction
    {
        return $this->target_minor_faction;
    }

    public function setTargetMinorFaction(?MinorFaction $target_minor_faction): self
    {
        $this->target_minor_faction = $target_minor_faction;

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
}
