<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PlatformRepository")
 */
class Platform
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=20)
     */
    private $name;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Squadron", mappedBy="platform")
     */
    private $squadrons;

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
            $squadron->setPlatform($this);
        }

        return $this;
    }

    public function removeSquadron(Squadron $squadron): self
    {
        if ($this->squadrons->contains($squadron)) {
            $this->squadrons->removeElement($squadron);
            // set the owning side to null (unless already changed)
            if ($squadron->getPlatform() === $this) {
                $squadron->setPlatform(null);
            }
        }

        return $this;
    }

    public function __toString()
    {
        return $this->name;
    }
}
