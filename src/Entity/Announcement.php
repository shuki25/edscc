<?php

namespace App\Entity;

use App\Entity\Traits\MyBlameableEntity;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * @ORM\Entity(repositoryClass="App\Repository\AnnouncementRepository")
 */
class Announcement
{
    use TimestampableEntity;
    use MyBlameableEntity;

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
     * @ORM\ManyToOne(targetEntity="App\Entity\Squadron", inversedBy="announcements")
     * @ORM\JoinColumn(nullable=false)
     */
    private $squadron;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $title;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $message;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $publish_at;

    /**
     * @ORM\Column(type="boolean", options={"default":"0"})
     */
    private $published_flag = 0;

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

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
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

    public function getPublishAt(): ?\DateTimeInterface
    {
        return $this->publish_at;
    }

    public function setPublishAt(?\DateTimeInterface $publish_at): self
    {
        $this->publish_at = $publish_at;

        return $this;
    }

    public function getPublishedFlag(): ?bool
    {
        return $this->published_flag;
    }

    public function setPublishedFlag(bool $published_flag): self
    {
        $this->published_flag = $published_flag;

        return $this;
    }

}
