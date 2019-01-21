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
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    private $player_id;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\User", inversedBy="commander", cascade={"persist", "remove"})
     */
    private $user;

    /**
     * @ORM\Column(type="bigint", nullable=true)
     */
    private $asset = 0;

    /**
     * @ORM\Column(type="bigint", nullable=true)
     */
    private $credits = 0;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $loan = 0;

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

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $combat_progress = 0;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $trade_progress = 0;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $explore_progress = 0;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $federation_progress = 0;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $empire_progress = 0;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $cqc_progress = 0;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPlayerId(): ?string
    {
        return $this->player_id;
    }

    public function setPlayerId(?string $player_id): self
    {
        $this->player_id = $player_id;

        return $this;
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

    public function getAsset(): ?int
    {
        return $this->asset;
    }

    public function setAsset(?int $asset): self
    {
        $this->asset = $asset;

        return $this;
    }

    public function getCredits(): ?int
    {
        return $this->credits;
    }

    public function setCredits(int $credits): self
    {
        $this->credits = $credits;

        return $this;
    }

    public function getLoan(): ?int
    {
        return $this->loan;
    }

    public function setLoan(?int $loan): self
    {
        $this->loan = $loan;

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

    public function getCombatProgress(): ?int
    {
        return $this->combat_progress;
    }

    public function setCombatProgress(?int $combat_progress): self
    {
        $this->combat_progress = $combat_progress;

        return $this;
    }

    public function getTradeProgress(): ?int
    {
        return $this->trade_progress;
    }

    public function setTradeProgress(?int $trade_progress): self
    {
        $this->trade_progress = $trade_progress;

        return $this;
    }

    public function getExploreProgress(): ?int
    {
        return $this->explore_progress;
    }

    public function setExploreProgress(?int $explore_progress): self
    {
        $this->explore_progress = $explore_progress;

        return $this;
    }

    public function getFederationProgress(): ?int
    {
        return $this->federation_progress;
    }

    public function setFederationProgress(?int $federation_progress): self
    {
        $this->federation_progress = $federation_progress;

        return $this;
    }

    public function getEmpireProgress(): ?int
    {
        return $this->empire_progress;
    }

    public function setEmpireProgress(int $empire_progress): self
    {
        $this->empire_progress = $empire_progress;

        return $this;
    }

    public function getCqcProgress(): ?int
    {
        return $this->cqc_progress;
    }

    public function setCqcProgress(?int $cqc_progress): self
    {
        $this->cqc_progress = $cqc_progress;

        return $this;
    }

    public function setRankId($entity, $object)
    {
        switch ($entity) {
            case 'Combat':
                $this->setCombat($object);
            case 'Trade':
                $this->setTrade($object);
            case 'Explore':
                $this->setExplore($object);
            case 'Federation':
                $this->setFederation($object);
            case 'Empire':
                $this->setEmpire($object);
            case 'CQC':
                $this->setCqc($object);
        }
        return $this;
    }

    public function setRankProgress($entity, $object)
    {
        switch ($entity) {
            case 'Combat':
                $this->setCombatProgress($object);
            case 'Trade':
                $this->setTradeProgress($object);
            case 'Explore':
                $this->setExploreProgress($object);
            case 'Federation':
                $this->setFederationProgress($object);
            case 'Empire':
                $this->setEmpireProgress($object);
            case 'CQC':
                $this->setCqcProgress($object);
        }
        return $this;
    }
}
