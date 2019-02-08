<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\SquadronTagsRepository")
 */
class SquadronTags
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Squadron", inversedBy="squadronTags")
     * @ORM\JoinColumn(nullable=false)
     */
    private $squadron;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Tags")
     * @ORM\JoinColumn(nullable=false)
     */
    private $tag;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getTag(): ?Tags
    {
        return $this->tag;
    }

    public function setTag(?Tags $tag): self
    {
        $this->tag = $tag;

        return $this;
    }
}
