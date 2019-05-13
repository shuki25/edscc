<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ErrorLogRepository")
 */
class ErrorLog
{
    Use TimestampableEntity;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $scope;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $error_id;

    /**
     * @ORM\Column(type="text")
     */
    private $error_msg;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $debug_info;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $stack_trace;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $data_trace;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getScope(): ?string
    {
        return $this->scope;
    }

    public function setScope(string $scope): self
    {
        $this->scope = $scope;

        return $this;
    }

    public function getErrorMsg(): ?string
    {
        return $this->error_msg;
    }

    public function setErrorMsg(string $error_msg): self
    {
        $this->error_msg = $error_msg;

        return $this;
    }

    public function getDebugInfo(): ?string
    {
        return $this->debug_info;
    }

    public function setDebugInfo(?string $debug_info): self
    {
        $this->debug_info = $debug_info;

        return $this;
    }

    public function getStackTrace(): ?string
    {
        return $this->stack_trace;
    }

    public function setStackTrace(?string $stack_trace): self
    {
        $this->stack_trace = $stack_trace;

        return $this;
    }

    public function getErrorId(): ?string
    {
        return $this->error_id;
    }

    public function setErrorId(?string $error_id): self
    {
        $this->error_id = $error_id;

        return $this;
    }

    public function getDataTrace(): ?string
    {
        return $this->data_trace;
    }

    public function setDataTrace(?string $data_trace): self
    {
        $this->data_trace = $data_trace;

        return $this;
    }
}
