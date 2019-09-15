<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ThargoidActivityRepository")
 */
class ThargoidActivity
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="thargoidActivities")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Squadron", inversedBy="thargoidActivities")
     * @ORM\JoinColumn(nullable=false)
     */
    private $squadron;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\MinorFaction")
     */
    private $minor_faction;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\ThargoidVariant", inversedBy="thargoidActivities")
     * @ORM\JoinColumn(nullable=false)
     */
    private $thargoid;

    /**
     * @ORM\Column(type="integer")
     */
    private $reward;

    /**
     * @ORM\Column(type="datetime")
     */
    private $date_killed;

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

    public function getMinorFaction(): ?MinorFaction
    {
        return $this->minor_faction;
    }

    public function setMinorFaction(?MinorFaction $minor_faction): self
    {
        $this->minor_faction = $minor_faction;

        return $this;
    }

    public function getThargoid(): ?ThargoidVariant
    {
        return $this->thargoid;
    }

    public function setThargoid(?ThargoidVariant $thargoid): self
    {
        $this->thargoid = $thargoid;

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

    public function getDateKilled(): ?\DateTimeInterface
    {
        return $this->date_killed;
    }

    public function setDateKilled(\DateTimeInterface $date_killed): self
    {
        $this->date_killed = $date_killed;

        return $this;
    }
}
