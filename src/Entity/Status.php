<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\StatusRepository")
 */
class Status
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=20)
     */
    private $name;

    /**
     * @ORM\Column(type="boolean")
     */
    private $lock_out_flag;

    /**
     * @ORM\Column(type="boolean")
     */
    private $banned_flag;

    /**
     * @ORM\Column(type="boolean")
     */
    private $active_flag;

    /**
     * @ORM\Column(type="boolean")
     */
    private $denied_flag;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    private $tag;

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

    public function getLockOutFlag(): ?bool
    {
        return $this->lock_out_flag;
    }

    public function setLockOutFlag(bool $lock_out_flag): self
    {
        $this->lock_out_flag = $lock_out_flag;

        return $this;
    }

    public function getBannedFlag(): ?bool
    {
        return $this->banned_flag;
    }

    public function setBannedFlag(bool $banned_flag): self
    {
        $this->banned_flag = $banned_flag;

        return $this;
    }

    public function getActiveFlag(): ?bool
    {
        return $this->active_flag;
    }

    public function setActiveFlag(bool $active_flag): self
    {
        $this->active_flag = $active_flag;

        return $this;
    }

    public function getDeniedFlag(): ?bool
    {
        return $this->denied_flag;
    }

    public function setDeniedFlag(bool $denied_flag): self
    {
        $this->denied_flag = $denied_flag;

        return $this;
    }

    public function getTag(): ?string
    {
        return $this->tag;
    }

    public function setTag(?string $tag): self
    {
        $this->tag = $tag;

        return $this;
    }
}
