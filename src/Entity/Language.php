<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\LanguageRepository")
 */
class Language
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=2)
     */
    private $locale;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $locale_name;

    /**
     * @ORM\Column(type="boolean")
     */
    private $has_translation;

    /**
     * @ORM\Column(type="smallint")
     */
    private $percent_complete;

    /**
     * @ORM\Column(type="boolean")
     */
    private $verified;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLocale(): ?string
    {
        return $this->locale;
    }

    public function setLocale(string $locale): self
    {
        $this->locale = $locale;

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

    public function getLocaleName(): ?string
    {
        return $this->locale_name;
    }

    public function setLocaleName(string $locale_name): self
    {
        $this->locale_name = $locale_name;

        return $this;
    }

    public function getHasTranslation(): ?bool
    {
        return $this->has_translation;
    }

    public function setHasTranslation(bool $has_translation): self
    {
        $this->has_translation = $has_translation;

        return $this;
    }

    public function getPercentComplete(): ?int
    {
        return $this->percent_complete;
    }

    public function setPercentComplete(int $percent_complete): self
    {
        $this->percent_complete = $percent_complete;

        return $this;
    }

    public function getVerified(): ?bool
    {
        return $this->verified;
    }

    public function setVerified(bool $verified): self
    {
        $this->verified = $verified;

        return $this;
    }
}
