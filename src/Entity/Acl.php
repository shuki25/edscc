<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\AclRepository")
 */
class Acl
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $role_string;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $description;

    /**
     * @ORM\Column(type="smallint")
     */
    private $list_order;

    /**
     * @ORM\Column(type="boolean")
     */
    private $admin_flag;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRoleString(): ?string
    {
        return $this->role_string;
    }

    public function setRoleString(string $role_string): self
    {
        $this->role_string = $role_string;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getListOrder(): ?int
    {
        return $this->list_order;
    }

    public function setListOrder(int $list_order): self
    {
        $this->list_order = $list_order;

        return $this;
    }

    public function getAdminFlag(): ?bool
    {
        return $this->admin_flag;
    }

    public function setAdminFlag(bool $admin_flag): self
    {
        $this->admin_flag = $admin_flag;

        return $this;
    }
}
