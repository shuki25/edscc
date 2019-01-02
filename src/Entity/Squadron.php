<?php

namespace App\Entity;

use App\Repository\SquadronRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\SquadronRepository")
 */
class Squadron
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\User", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $admin;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\User", mappedBy="Squadron")
     */
    private $user;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $welcome_message;

    /**
     * @ORM\Column(type="string", length=1, options={"default": "Y"})
     */
    private $RequireApproval = 'Y';

    /**
     * @var SquadronRepository
     */
    private $squadronRepository;

    public function __construct(SquadronRepository $squadronRepository)
    {
        $this->user = new ArrayCollection();
        $this->squadronRepository = $squadronRepository;
    }

    /**
     * @return Collection|Squadron[]
     */
    public function getAll(): Collection
    {
        $this->squadronRepository->findAll();
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

    public function getAdmin(): ?User
    {
        return $this->admin;
    }

    public function setAdmin(User $admin): self
    {
        $this->admin = $admin;

        return $this;
    }

    /**
     * @return Collection|User[]
     */
    public function getUser(): Collection
    {
        return $this->user;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getWelcomeMessage(): ?string
    {
        return $this->welcome_message;
    }

    public function setWelcomeMessage(?string $welcome_message): self
    {
        $this->welcome_message = $welcome_message;

        return $this;
    }

    public function getRequireApproval(): ?string
    {
        return $this->RequireApproval;
    }

    public function setRequireApproval(?string $RequireApproval): self
    {
        $this->RequireApproval = $RequireApproval;

        return $this;
    }
}
