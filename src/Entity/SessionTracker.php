<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\SessionTrackerRepository")
 */
class SessionTracker
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="sessionTrackers")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\Column(type="boolean")
     */
    private $api_flag;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $session_data;

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

    public function getApiFlag(): ?bool
    {
        return $this->api_flag;
    }

    public function setApiFlag(bool $api_flag): self
    {
        $this->api_flag = $api_flag;

        return $this;
    }

    public function getSessionData(): ?array
    {
        return json_decode($this->session_data, true);
    }

    public function setSessionData(?array $session_data): self
    {
        $this->session_data = json_encode($session_data);

        return $this;
    }
}
