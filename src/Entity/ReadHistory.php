<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ReadHistoryRepository")
 */
class ReadHistory
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Announcement")
     */
    private $announcement;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Motd")
     */
    private $motd;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="readHistories")
     */
    private $user;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAnnouncement(): ?Announcement
    {
        return $this->announcement;
    }

    public function setAnnouncement(?Announcement $announcement): self
    {
        $this->announcement = $announcement;

        return $this;
    }

    public function getMotd(): ?Motd
    {
        return $this->motd;
    }

    public function setMotd(?Motd $motd): self
    {
        $this->motd = $motd;

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
}
