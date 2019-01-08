<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PowerRepository")
 */
class Power
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $logo;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Squadron", mappedBy="power")
     */
    private $squadrons;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $journal_id;

    /**
     * @ORM\Column(type="string", length=7, nullable=true)
     */
    private $color_power;

    public function __construct()
    {
        $this->squadrons = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getLogo(): ?string
    {
        return $this->logo;
    }

    public function setLogo(?string $logo): self
    {
        $this->logo = $logo;

        return $this;
    }

    /**
     * @return Collection|Squadron[]
     */
    public function getSquadrons(): Collection
    {
        return $this->squadrons;
    }

    public function addSquadron(Squadron $squadron): self
    {
        if (!$this->squadrons->contains($squadron)) {
            $this->squadrons[] = $squadron;
            $squadron->setPower($this);
        }

        return $this;
    }

    public function removeSquadron(Squadron $squadron): self
    {
        if ($this->squadrons->contains($squadron)) {
            $this->squadrons->removeElement($squadron);
            // set the owning side to null (unless already changed)
            if ($squadron->getPower() === $this) {
                $squadron->setPower(null);
            }
        }

        return $this;
    }

    public function getJournalId(): ?int
    {
        return $this->journal_id;
    }

    public function setJournalId(?int $journal_id): self
    {
        $this->journal_id = $journal_id;

        return $this;
    }

    public function getColorPower(): ?string
    {
        return $this->color_power;
    }

    public function setColorPower(?string $color_power): self
    {
        $this->color_power = $color_power;

        return $this;
    }

    public function __toString()
    {
        return $this->name;
    }

}
