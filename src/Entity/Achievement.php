<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\AchievementRepository")
 */
class Achievement
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="achievements")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\Column(type="boolean")
     */
    private $view_flag;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\AchievementRule")
     * @ORM\JoinColumn(nullable=false)
     */
    private $achievement_rule;

    /**
     * @ORM\Column(type="datetime")
     */
    private $date_unlocked;

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

    public function getViewFlag(): ?bool
    {
        return $this->view_flag;
    }

    public function setViewFlag(bool $view_flag): self
    {
        $this->view_flag = $view_flag;

        return $this;
    }

    public function getAchievementRule(): ?AchievementRule
    {
        return $this->achievement_rule;
    }

    public function setAchievementRule(?AchievementRule $achievement_rule): self
    {
        $this->achievement_rule = $achievement_rule;

        return $this;
    }

    public function getDateUnlocked(): ?\DateTimeInterface
    {
        return $this->date_unlocked;
    }

    public function setDateUnlocked(\DateTimeInterface $date_unlocked): self
    {
        $this->date_unlocked = $date_unlocked;

        return $this;
    }
}
