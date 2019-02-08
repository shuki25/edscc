<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\TagsRepository")
 */
class Tags
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer", options={"unsigned"=true})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=20)
     */
    private $group_code;

    /**
     * @ORM\Column(type="string", length=40)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    private $badge_color;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getGroupCode(): ?string
    {
        return $this->group_code;
    }

    public function setGroupCode(string $group_code): self
    {
        $this->group_code = $group_code;

        return $this;
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

    public function getBadgeColor(): ?string
    {
        return $this->badge_color;
    }

    public function setBadgeColor(?string $badge_color): self
    {
        $this->badge_color = $badge_color;

        return $this;
    }
}
