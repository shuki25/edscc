<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Oauth2Repository")
 *  * @ORM\Table(indexes={@ORM\Index(name="sync_status_idx", columns={"sync_status"}),
 *     @ORM\Index(name="keep_alive_idx", columns={"keep_alive"}),
 *     @ORM\Index(name="auto_download_idx", columns={"auto_download"})})
 */
class Oauth2
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\User", inversedBy="oauth2", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\Column(type="string", length=4096)
     */
    private $access_token;

    /**
     * @ORM\Column(type="string", length=32)
     */
    private $token_type;

    /**
     * @ORM\Column(type="integer")
     */
    private $expires_in;

    /**
     * @ORM\Column(type="string", length=256)
     */
    private $refresh_token;

    /**
     * @ORM\Column(type="boolean")
     */
    private $connection_flag = 0;

    /**
     * @ORM\Column(type="boolean")
     */
    private $auto_download = 0;

    /**
     * @ORM\Column(type="boolean")
     */
    private $keep_alive = 0;

    /**
     * @ORM\Column(type="boolean")
     */
    private $sync_status = 0;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $last_fetched_on;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getAccessToken(): ?string
    {
        return $this->access_token;
    }

    public function setAccessToken(string $access_token): self
    {
        $this->access_token = $access_token;

        return $this;
    }

    public function getTokenType(): ?string
    {
        return $this->token_type;
    }

    public function setTokenType(string $token_type): self
    {
        $this->token_type = $token_type;

        return $this;
    }

    public function getExpiresIn(): ?int
    {
        return $this->expires_in;
    }

    public function setExpiresIn(int $expires_in): self
    {
        $this->expires_in = $expires_in;

        return $this;
    }

    public function getRefreshToken(): ?string
    {
        return $this->refresh_token;
    }

    public function setRefreshToken(string $refresh_token): self
    {
        $this->refresh_token = $refresh_token;

        return $this;
    }

    public function getConnectionFlag(): ?bool
    {
        return $this->connection_flag;
    }

    public function setConnectionFlag(bool $connection_flag): self
    {
        $this->connection_flag = $connection_flag;

        return $this;
    }

    public function getAutoDownload(): ?bool
    {
        return $this->auto_download;
    }

    public function setAutoDownload(bool $auto_download): self
    {
        $this->auto_download = $auto_download;

        return $this;
    }

    public function getKeepAlive(): ?bool
    {
        return $this->keep_alive;
    }

    public function setKeepAlive(bool $keep_alive): self
    {
        $this->keep_alive = $keep_alive;

        return $this;
    }

    public function getLastFetchedOn(): ?\DateTimeInterface
    {
        return $this->last_fetched_on;
    }

    public function setLastFetchedOn(?\DateTimeInterface $last_fetched_on): self
    {
        $this->last_fetched_on = $last_fetched_on;

        return $this;
    }

    public function getSyncStatus(): ?bool
    {
        return $this->sync_status;
    }

    public function setSyncStatus(bool $sync_status): self
    {
        $this->sync_status = $sync_status;

        return $this;
    }
}
