<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 */
class User implements UserInterface
{
    Use TimestampableEntity;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     */
    private $email;

    /**
     * @ORM\Column(type="json")
     */
    private $roles = [];

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     */
    private $password;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $commander_name;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Squadron", inversedBy="user")
     */
    private $Squadron = '1';

    /**
     * @ORM\Column(type="string", length=1, options={"default": "N"})
     */
    private $email_verify = 'N';

    /**
     * @ORM\Column(type="string", length=32, nullable=true)
     */
    private $oauth_id;

    /**
     * @ORM\Column(type="string", length=1, options={"default": "N"})
     */
    private $google_flag = 'N';

    /**
     * @ORM\Column(type="string", length=1, options={"default": "N"})
     */
    private $gravatar_flag = 'N';

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $avatar_url;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\VerifyToken", mappedBy="User", orphanRemoval=true)
     */
    private $verifyTokens;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $LastLoginAt;

    public function __construct()
    {
        $this->verifyTokens = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUsername(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getPassword(): string
    {
        return (string) $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getSalt()
    {
        // not needed when using the "bcrypt" algorithm in security.yaml
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getCommanderName(): ?string
    {
        return $this->commander_name;
    }

    public function setCommanderName(string $commander_name): self
    {
        $this->commander_name = $commander_name;

        return $this;
    }

    /**
     * @return Squadron|null
     */
    public function getSquadron(): ?Squadron
    {
        return $this->Squadron;
    }

    public function setSquadron(?Squadron $Squadron): self
    {
        $this->Squadron = $Squadron;

        return $this;
    }

    public function getEmailVerify(): ?string
    {
        return $this->email_verify;
    }

    public function setEmailVerify(string $email_verify): self
    {
        $this->email_verify = $email_verify;

        return $this;
    }

    public function getOauthId(): ?string
    {
        return $this->oauth_id;
    }

    public function setOauthId(?string $oauth_id): self
    {
        $this->oauth_id = $oauth_id;

        return $this;
    }

    public function getGoogleFlag(): ?string
    {
        return $this->google_flag;
    }

    public function setGoogleFlag(string $google_flag): self
    {
        $this->google_flag = $google_flag;

        return $this;
    }

    public function getGravatarFlag(): ?string
    {
        return $this->gravatar_flag;
    }

    public function setGravatarFlag(string $gravatar_flag): self
    {
        $this->gravatar_flag = $gravatar_flag;

        return $this;
    }

    public function getAvatarUrl(): ?string
    {
        return $this->avatar_url;
    }

    public function setAvatarUrl(?string $avatar_url): self
    {
        $this->avatar_url = $avatar_url;

        return $this;
    }

    public function getAvatarImgUrl(int $size = null): string
    {
        if($this->getGravatarFlag() == "Y") {
            $hash = md5(strtolower(trim($this->getUsername())));
            $url = sprintf("https://www.gravatar.com/avatar/%s?d=mp", $hash);
            if($size) {
                $url .= sprintf("&s=%d", $size);
            }
            return $url;
        }
        else if($this->getAvatarUrl()) {
            return $this->getAvatarUrl();
        }
        return "";
    }

    public function getLastLoginAt()
    {
        return $this->LastLoginAt;
    }

    public function setLastLoginAt($LastLoginAt = null): void
    {
        $this->LastLoginAt = isset($LastLoginAt) ? $LastLoginAt : new \DateTime('now');
    }

    /**
     * @return Collection|VerifyToken[]
     */
    public function getVerifyTokens(): Collection
    {
        return $this->verifyTokens;
    }

    public function addVerifyToken(VerifyToken $verifyToken): self
    {
        if (!$this->verifyTokens->contains($verifyToken)) {
            $this->verifyTokens[] = $verifyToken;
            $verifyToken->setUser($this);
        }

        return $this;
    }

    public function removeVerifyToken(VerifyToken $verifyToken): self
    {
        if ($this->verifyTokens->contains($verifyToken)) {
            $this->verifyTokens->removeElement($verifyToken);
            // set the owning side to null (unless already changed)
            if ($verifyToken->getUser() === $this) {
                $verifyToken->setUser(null);
            }
        }

        return $this;
    }
}
