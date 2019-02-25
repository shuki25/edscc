<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * @ORM\Entity(repositoryClass="App\Repository\MotdRepository")
 */
class Motd
{
    Use TimestampableEntity;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $title;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $message;

    /**
     * @ORM\Column(type="boolean")
     */
    private $show_flag;

    /**
     * @ORM\Column(type="boolean")
     */
    private $show_login;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(?string $message): self
    {
        $this->message = $message;

        return $this;
    }

    public function getShowFlag(): ?bool
    {
        return $this->show_flag;
    }

    public function setShowFlag(bool $show_flag): self
    {
        $this->show_flag = $show_flag;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getShowLogin(): ?bool
    {
        return $this->show_login;
    }

    public function setShowLogin(bool $show_login): self
    {
        $this->show_login = $show_login;

        return $this;
    }
}
