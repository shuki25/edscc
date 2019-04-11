<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * @ORM\Entity(repositoryClass="App\Repository\AccessHistoryRepository")
 */
class AccessHistory
{
    Use TimestampableEntity;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="accessHistories")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\Column(type="string", length=16)
     */
    private $remote_ip;

    /**
     * @ORM\Column(type="string", length=2)
     */
    private $country_code;

    /**
     * @ORM\Column(type="string", length=64)
     */
    private $country_name;

    /**
     * @ORM\Column(type="string", length=128)
     */
    private $region_name;

    /**
     * @ORM\Column(type="string", length=128)
     */
    private $city_name;

    /**
     * @ORM\Column(type="float")
     */
    private $latitude;

    /**
     * @ORM\Column(type="float")
     */
    private $longitude;

    /**
     * @ORM\Column(type="string", length=30)
     */
    private $zip_code;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $browser;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $platform;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $device;

    /**
     * @ORM\Column(type="boolean")
     */
    private $google_2fa_trust_flag = 0;

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

    public function getRemoteIp(): ?string
    {
        return $this->remote_ip;
    }

    public function setRemoteIp(string $remote_ip): self
    {
        $this->remote_ip = $remote_ip;

        return $this;
    }

    public function getCountryCode(): ?string
    {
        return $this->country_code;
    }

    public function setCountryCode(string $country_code): self
    {
        $this->country_code = $country_code;

        return $this;
    }

    public function getCountryName(): ?string
    {
        return $this->country_name;
    }

    public function setCountryName(string $country_name): self
    {
        $this->country_name = $country_name;

        return $this;
    }

    public function getRegionName(): ?string
    {
        return $this->region_name;
    }

    public function setRegionName(string $region_name): self
    {
        $this->region_name = $region_name;

        return $this;
    }

    public function getLatitude(): ?float
    {
        return $this->latitude;
    }

    public function setLatitude(float $latitude): self
    {
        $this->latitude = $latitude;

        return $this;
    }

    public function getLongitude(): ?float
    {
        return $this->longitude;
    }

    public function setLongitude(float $longitude): self
    {
        $this->longitude = $longitude;

        return $this;
    }

    public function getZipCode(): ?string
    {
        return $this->zip_code;
    }

    public function setZipCode(string $zip_code): self
    {
        $this->zip_code = $zip_code;

        return $this;
    }

    public function getBrowser(): ?string
    {
        return $this->browser;
    }

    public function setBrowser(?string $browser): self
    {
        $this->browser = $browser;

        return $this;
    }

    public function getPlatform(): ?string
    {
        return $this->platform;
    }

    public function setPlatform(?string $platform): self
    {
        $this->platform = $platform;

        return $this;
    }

    public function getCityName(): ?string
    {
        return $this->city_name;
    }

    public function setCityName(string $city_name): self
    {
        $this->city_name = $city_name;

        return $this;
    }

    public function getDevice(): ?string
    {
        return $this->device;
    }

    public function setDevice(?string $device): self
    {
        $this->device = $device;

        return $this;
    }

    public function getGoogle2faTrustFlag(): ?bool
    {
        return $this->google_2fa_trust_flag;
    }

    public function setGoogle2faTrustFlag(bool $google_2fa_trust_flag): self
    {
        $this->google_2fa_trust_flag = $google_2fa_trust_flag;

        return $this;
    }
}
