<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ActivityCounterRepository")
 * @ORM\Table(indexes={@ORM\Index(name="activity_date_idx", columns={"activity_date"})})
 */
class ActivityCounter
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
    private $activity_date;

    /**
     * @ORM\Column(type="integer")
     */
    private $bounties_claimed = 0;

    /**
     * @ORM\Column(type="integer")
     */
    private $systems_scanned = 0;

    /**
     * @ORM\Column(type="integer")
     */
    private $bodies_found = 0;

    /**
     * @ORM\Column(type="integer")
     */
    private $saa_scan_completed = 0;

    /**
     * @ORM\Column(type="integer")
     */
    private $efficiency_achieved = 0;

    /**
     * @ORM\Column(type="integer")
     */
    private $market_buy = 0;

    /**
     * @ORM\Column(type="integer")
     */
    private $market_sell = 0;

    /**
     * @ORM\Column(type="integer")
     */
    private $missions_completed = 0;

    /**
     * @ORM\Column(type="integer")
     */
    private $mining_refined = 0;

    /**
     * @ORM\Column(type="integer")
     */
    private $stolen_goods = 0;

    /**
     * @ORM\Column(type="integer")
     */
    private $cg_participated = 0;

    /**
     * @ORM\Column(type="integer")
     */
    private $crimes_committed = 0;

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

    public function getActivityDate(): ?\DateTimeInterface
    {
        return $this->activity_date;
    }

    public function setActivityDate(\DateTimeInterface $activity_date): self
    {
        $this->activity_date = $activity_date;

        return $this;
    }

    public function getBountiesClaimed(): ?int
    {
        return $this->bounties_claimed;
    }

    public function addBountiesClaimed(int $bounties_claimed): self
    {
        $this->bounties_claimed += $bounties_claimed;

        return $this;
    }

    public function getSystemsScanned(): ?int
    {
        return $this->systems_scanned;
    }

    public function addSystemsScanned(int $systems_scanned): self
    {
        $this->systems_scanned += $systems_scanned;

        return $this;
    }

    public function getBodiesFound(): ?int
    {
        return $this->bodies_found;
    }

    public function addBodiesFound(int $bodies_found): self
    {
        $this->bodies_found += $bodies_found;

        return $this;
    }

    public function getSaaScanCompleted(): ?int
    {
        return $this->saa_scan_completed;
    }

    public function addSaaScanCompleted(int $saa_scan_completed): self
    {
        $this->saa_scan_completed += $saa_scan_completed;

        return $this;
    }

    public function getEfficiencyAchieved(): ?int
    {
        return $this->efficiency_achieved;
    }

    public function addEfficiencyAchieved(int $efficiency_achieved): self
    {
        $this->efficiency_achieved += $efficiency_achieved;

        return $this;
    }

    public function getMarketBuy(): ?int
    {
        return $this->market_buy;
    }

    public function addMarketBuy(int $market_buy): self
    {
        $this->market_buy += $market_buy;

        return $this;
    }

    public function getMarketSell(): ?int
    {
        return $this->market_sell;
    }

    public function addMarketSell(int $market_sell): self
    {
        $this->market_sell += $market_sell;

        return $this;
    }

    public function getMissionsCompleted(): ?int
    {
        return $this->missions_completed;
    }

    public function addMissionsCompleted(int $missions_completed): self
    {
        $this->missions_completed += $missions_completed;

        return $this;
    }

    public function getMiningRefined(): ?int
    {
        return $this->mining_refined;
    }

    public function addMiningRefined(int $mining_refined): self
    {
        $this->mining_refined += $mining_refined;

        return $this;
    }

    public function getStolenGoods(): ?int
    {
        return $this->stolen_goods;
    }

    public function addStolenGoods(int $stolen_goods): self
    {
        $this->stolen_goods += $stolen_goods;

        return $this;
    }

    public function getCgParticipated(): ?int
    {
        return $this->cg_participated;
    }

    public function addCgParticipated(int $cg_participated): self
    {
        $this->cg_participated += $cg_participated;

        return $this;
    }

    public function getCrimesCommitted(): ?int
    {
        return $this->crimes_committed;
    }

    public function addCrimesCommitted(int $crimes_committed): self
    {
        $this->crimes_committed += $crimes_committed;

        return $this;
    }
}
