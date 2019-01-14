<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CommanderRepository")
 */
class Commander
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $game_id;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\User", inversedBy="commander", cascade={"persist", "remove"})
     */
    private $user_id;

    /**
     * @ORM\Column(type="bigint")
     */
    private $asset;

    /**
     * @ORM\Column(type="bigint")
     */
    private $cash;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Rank")
     */
    private $combat;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Rank")
     */
    private $trade;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Rank")
     */
    private $explore;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Rank")
     */
    private $federation;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Rank")
     */
    private $empire;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Rank")
     */
    private $cqc;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getGameId(): ?int
    {
        return $this->game_id;
    }

    public function setGameId(?int $game_id): self
    {
        $this->game_id = $game_id;

        return $this;
    }

    public function getUserId(): ?User
    {
        return $this->user_id;
    }

    public function setUserId(?User $user_id): self
    {
        $this->user_id = $user_id;

        return $this;
    }

    public function getAsset(): ?int
    {
        return $this->asset;
    }

    public function setAsset(?int $asset): self
    {
        $this->asset = $asset;

        return $this;
    }

    public function getCash(): ?int
    {
        return $this->cash;
    }

    public function setCash(int $cash): self
    {
        $this->cash = $cash;

        return $this;
    }

    public function getCombat(): ?Rank
    {
        return $this->combat;
    }

    public function setCombat(?Rank $combat): self
    {
        $this->combat = $combat;

        return $this;
    }

    public function getTrade(): ?Rank
    {
        return $this->trade;
    }

    public function setTrade(?Rank $trade): self
    {
        $this->trade = $trade;

        return $this;
    }

    public function getExplore(): ?Rank
    {
        return $this->explore;
    }

    public function setExplore(?Rank $explore): self
    {
        $this->explore = $explore;

        return $this;
    }

    public function getCqc(): ?Rank
    {
        return $this->cqc;
    }

    public function setCqc(?Rank $cqc): self
    {
        $this->cqc = $cqc;

        return $this;
    }

    public function getFederation(): ?Rank
    {
        return $this->federation;
    }

    public function setFederation(?Rank $federation): self
    {
        $this->federation = $federation;

        return $this;
    }

    public function getEmpire(): ?Rank
    {
        return $this->empire;
    }

    public function setEmpire(?Rank $empire): self
    {
        $this->empire = $empire;

        return $this;
    }
}
