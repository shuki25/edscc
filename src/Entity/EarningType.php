<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\EarningTypeRepository")
 */
class EarningType
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=40)
     */
    private $name;

    /**
     * @ORM\Column(type="boolean")
     */
    private $mission_flag;

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

    public function getMissionFlag(): ?bool
    {
        return $this->mission_flag;
    }

    public function setMissionFlag(bool $mission_flag): self
    {
        $this->mission_flag = $mission_flag;

        return $this;
    }
}
