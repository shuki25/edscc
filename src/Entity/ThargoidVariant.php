<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ThargoidVariantRepository")
 */
class ThargoidVariant
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $name;

    /**
     * @ORM\Column(type="integer")
     */
    private $reward;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\ThargoidActivity", mappedBy="thargoid", orphanRemoval=true)
     */
    private $thargoidActivities;

    public function __construct()
    {
        $this->thargoidActivities = new ArrayCollection();
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

    public function getReward(): ?int
    {
        return $this->reward;
    }

    public function setReward(int $reward): self
    {
        $this->reward = $reward;

        return $this;
    }

    /**
     * @return Collection|ThargoidActivity[]
     */
    public function getThargoidActivities(): Collection
    {
        return $this->thargoidActivities;
    }

    public function addThargoidActivity(ThargoidActivity $thargoidActivity): self
    {
        if (!$this->thargoidActivities->contains($thargoidActivity)) {
            $this->thargoidActivities[] = $thargoidActivity;
            $thargoidActivity->setThargoid($this);
        }

        return $this;
    }

    public function removeThargoidActivity(ThargoidActivity $thargoidActivity): self
    {
        if ($this->thargoidActivities->contains($thargoidActivity)) {
            $this->thargoidActivities->removeElement($thargoidActivity);
            // set the owning side to null (unless already changed)
            if ($thargoidActivity->getThargoid() === $this) {
                $thargoidActivity->setThargoid(null);
            }
        }

        return $this;
    }
}
