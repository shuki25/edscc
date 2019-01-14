<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\RankRepository")
 */
class Rank
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
     * @ORM\Column(type="integer")
     */
    private $perm_mask;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\User", mappedBy="rank")
     */
    private $users;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    private $group_code;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $assigned_id;

    public function __construct()
    {
        $this->users = new ArrayCollection();
    }

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

    public function getPermMask(): ?int
    {
        return $this->perm_mask;
    }

    public function setPermMask(int $perm_mask): self
    {
        $this->perm_mask = $perm_mask;

        return $this;
    }

    /**
     * @return Collection|User[]
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): self
    {
        if (!$this->users->contains($user)) {
            $this->users[] = $user;
            $user->setRank($this);
        }

        return $this;
    }

    public function removeUser(User $user): self
    {
        if ($this->users->contains($user)) {
            $this->users->removeElement($user);
            // set the owning side to null (unless already changed)
            if ($user->getRank() === $this) {
                $user->setRank(null);
            }
        }

        return $this;
    }

    public function __toString()
    {
        return $this->name;
    }

    public function getGroupCode(): ?string
    {
        return $this->group_code;
    }

    public function setGroupCode(?string $group_code): self
    {
        $this->group_code = $group_code;

        return $this;
    }

    public function getAssignedId(): ?int
    {
        return $this->assigned_id;
    }

    public function setAssignedId(?int $assigned_id): self
    {
        $this->assigned_id = $assigned_id;

        return $this;
    }
}
