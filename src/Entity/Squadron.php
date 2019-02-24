<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\SquadronRepository")
 */
class Squadron
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank()
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=4, nullable=true)
     * @Assert\Length(max=4)
     */
    private $id_code;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Platform", inversedBy="squadrons")
     * @ORM\OrderBy({"name" = "ASC"})
     */
    private $platform;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\User", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $admin;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\User", mappedBy="Squadron")
     */
    private $user;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $welcome_message;

    /**
     * @ORM\Column(type="string", length=1, options={"default": "Y"})
     */
    private $RequireApproval = 'Y';

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Faction", inversedBy="squadrons")
     * @ORM\OrderBy({"name" = "ASC"})
     */
    private $faction;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Power", inversedBy="squadrons")
     * @ORM\OrderBy({"name" = "ASC"})
     */
    private $power;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $home_base;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Announcement", mappedBy="squadron", orphanRemoval=true)
     */
    private $announcements;

    /**
     * @ORM\Column(type="string", length=1, nullable=true)
     */
    private $active = 'Y';

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\SquadronTags", mappedBy="squadron")
     */
    private $squadronTags;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\CustomRank", mappedBy="squadron")
     */
    private $customRanks;

    public function __construct()
    {
        $this->user = new ArrayCollection();
        $this->announcements = new ArrayCollection();
        $this->squadronTags = new ArrayCollection();
        $this->customRanks = new ArrayCollection();
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

    public function getAdmin(): ?User
    {
        return $this->admin;
    }

    public function setAdmin(User $admin): self
    {
        $this->admin = $admin;

        return $this;
    }

    /**
     * @return Collection|User[]
     */
    public function getUser(): Collection
    {
        return $this->user;
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

    public function getWelcomeMessage(): ?string
    {
        return $this->welcome_message;
    }

    public function setWelcomeMessage(?string $welcome_message): self
    {
        $this->welcome_message = $welcome_message;

        return $this;
    }

    public function getRequireApproval(): ?string
    {
        return $this->RequireApproval;
    }

    public function setRequireApproval(?string $RequireApproval): self
    {
        $this->RequireApproval = $RequireApproval;

        return $this;
    }

    public function getFaction(): ?Faction
    {
        return $this->faction;
    }

    public function setFaction(?Faction $faction): self
    {
        $this->faction = $faction;

        return $this;
    }

    public function getPower(): ?Power
    {
        return $this->power;
    }

    public function setPower(?Power $power): self
    {
        $this->power = $power;

        return $this;
    }

    public function getHomeBase(): ?string
    {
        return $this->home_base;
    }

    public function setHomeBase(?string $home_base): self
    {
        $this->home_base = $home_base;

        return $this;
    }

    public function getIdCode(): ?string
    {
        return $this->id_code;
    }

    public function setIdCode(?string $id_code): self
    {
        $this->id_code = $id_code;

        return $this;
    }

    public function getPlatform(): ?Platform
    {
        return $this->platform;
    }

    public function setPlatform(?Platform $platform): self
    {
        $this->platform = $platform;

        return $this;
    }

    /**
     * @return Collection|Announcement[]
     */
    public function getAnnouncements(): Collection
    {
        return $this->announcements;
    }

    public function addAnnouncement(Announcement $announcement): self
    {
        if (!$this->announcements->contains($announcement)) {
            $this->announcements[] = $announcement;
            $announcement->setSquadron($this);
        }

        return $this;
    }

    public function removeAnnouncement(Announcement $announcement): self
    {
        if ($this->announcements->contains($announcement)) {
            $this->announcements->removeElement($announcement);
            // set the owning side to null (unless already changed)
            if ($announcement->getSquadron() === $this) {
                $announcement->setSquadron(null);
            }
        }

        return $this;
    }

    public function getActive(): ?string
    {
        return $this->active;
    }

    public function setActive(?string $active): self
    {
        $this->active = $active;

        return $this;
    }

    /**
     * @return Collection|SquadronTags[]
     */
    public function getSquadronTags(): Collection
    {
        return $this->squadronTags;
    }

    public function addSquadronTag(SquadronTags $squadronTag): self
    {
        if (!$this->squadronTags->contains($squadronTag)) {
            $this->squadronTags[] = $squadronTag;
            $squadronTag->setSquadron($this);
        }

        return $this;
    }

    public function removeSquadronTag(SquadronTags $squadronTag): self
    {
        if ($this->squadronTags->contains($squadronTag)) {
            $this->squadronTags->removeElement($squadronTag);
            // set the owning side to null (unless already changed)
            if ($squadronTag->getSquadron() === $this) {
                $squadronTag->setSquadron(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|CustomRank[]
     */
    public function getCustomRanks(): Collection
    {
        return $this->customRanks;
    }

    public function addCustomRank(CustomRank $customRank): self
    {
        if (!$this->customRanks->contains($customRank)) {
            $this->customRanks[] = $customRank;
            $customRank->setSquadron($this);
        }

        return $this;
    }

    public function removeCustomRank(CustomRank $customRank): self
    {
        if ($this->customRanks->contains($customRank)) {
            $this->customRanks->removeElement($customRank);
            // set the owning side to null (unless already changed)
            if ($customRank->getSquadron() === $this) {
                $customRank->setSquadron(null);
            }
        }

        return $this;
    }
}
