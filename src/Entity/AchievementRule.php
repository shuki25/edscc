<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\AchievementRuleRepository")
 */
class AchievementRule
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
     * @ORM\Column(type="string", length=512, nullable=true)
     */
    private $description;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\AchievementCondition", mappedBy="achievement_rule", orphanRemoval=true)
     */
    private $achievementConditions;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $trophy_image;

    public function __construct()
    {
        $this->achievementConditions = new ArrayCollection();
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return Collection|AchievementCondition[]
     */
    public function getAchievementConditions(): Collection
    {
        return $this->achievementConditions;
    }

    public function addAchievementCondition(AchievementCondition $achievementCondition): self
    {
        if (!$this->achievementConditions->contains($achievementCondition)) {
            $this->achievementConditions[] = $achievementCondition;
            $achievementCondition->setAchievementRule($this);
        }

        return $this;
    }

    public function removeAchievementCondition(AchievementCondition $achievementCondition): self
    {
        if ($this->achievementConditions->contains($achievementCondition)) {
            $this->achievementConditions->removeElement($achievementCondition);
            // set the owning side to null (unless already changed)
            if ($achievementCondition->getAchievementRule() === $this) {
                $achievementCondition->setAchievementRule(null);
            }
        }

        return $this;
    }

    public function getTrophyImage(): ?string
    {
        return $this->trophy_image;
    }

    public function setTrophyImage(?string $trophy_image): self
    {
        $this->trophy_image = $trophy_image;

        return $this;
    }
}
