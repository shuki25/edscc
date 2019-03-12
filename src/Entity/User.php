<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @ORM\Table(indexes={@ORM\Index(name="apikey_idx", columns={"apikey"})})
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
     * @ORM\Column(type="text")
     */
    private $roles = "[]";

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     */
    private $password;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $tmp_password;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $commander_name;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Squadron", inversedBy="user")
     */
    private $Squadron = '1';

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Rank", inversedBy="users")
     * @ORM\JoinColumn(nullable=true)
     */
    private $rank = '1';

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\CustomRank")
     */
    private $custom_rank = '1';

    /**
     * @ORM\Column(type="string", length=1, options={"default": "N"})
     */
    private $email_verify = 'N';

    /**
     * @ORM\Column(type="string", length=1, options={"default": "N"}, nullable=true)
     */
    private $welcome_message_flag = 'N';

    /**
     * @ORM\Column(type="string", length=64, nullable=true)
     */
    private $apikey;

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
     * @ORM\OrderBy({"expiresAt" = "DESC"})
     */
    private $verifyTokens;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $date_joined;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $LastLoginAt;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Status")
     * @ORM\JoinColumn(nullable=false)
     */
    private $status;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $status_comment;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Commander", mappedBy="user", cascade={"persist", "remove"})
     */
    private $commander;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\ImportQueue", mappedBy="user", orphanRemoval=true)
     */
    private $importQueues;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\SessionTracker", mappedBy="user")
     */
    private $sessionTrackers;

    public function __construct()
    {
        $this->verifyTokens = new ArrayCollection();
        $this->importQueues = new ArrayCollection();
        $this->sessionTrackers = new ArrayCollection();
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
        return (string)$this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = json_decode($this->roles, true);
        // guarantee every user at least has ROLE_USER


        if ($this->getStatus()->getName() != "Approved" && $this->getStatus()->getDeniedFlag() == false && $this->getEmailVerify() == "Y") {
            $roles = ['ROLE_PENDING'];
        } elseif ($this->getEmailVerify() == "N" || $this->getStatus()->getDeniedFlag()) {
            $roles = ['ROLE_DENIED'];
        } else {
            $roles[] = 'ROLE_USER';
        }

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = json_encode($roles);

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getPassword(): string
    {
        return (string)$this->password;
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

    public function getApikey(): ?string
    {
        return $this->apikey;
    }

    public function setApikey(?string $apikey): self
    {
        $this->apikey = $apikey;

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
        if ($this->getGravatarFlag() == "Y") {
            $hash = md5(strtolower(trim($this->getUsername())));
            $url = sprintf("https://www.gravatar.com/avatar/%s?d=mp", $hash);
            if ($size) {
                $url .= sprintf("&s=%d", $size);
            }
            return $url;
        } else if ($this->getAvatarUrl()) {
            return $this->getAvatarUrl();
        }
        return "";
    }

    public function getDateJoined(): ?\DateTimeInterface
    {
        return $this->date_joined;
    }

    public function setDateJoined(?\DateTimeInterface $date_joined): self
    {
        $this->date_joined = isset($date_joined) ? $date_joined : new \DateTime('now');
        return $this;
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

    public function getNewestVerifyTokens(): VerifyToken
    {
        if ($this->verifyTokens->isEmpty()) {
            return new VerifyToken();
        }
        return $this->verifyTokens->first();
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

    public function __toString()
    {
        return $this->getCommanderName();
    }

    public function getRank(): ?Rank
    {
        return $this->rank;
    }

    public function setRank(?Rank $rank): self
    {
        $this->rank = $rank;

        return $this;
    }

    public function getStatus(): ?Status
    {
        return $this->status;
    }

    public function setStatus(?Status $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getCommander(): ?Commander
    {
        return $this->commander;
    }

    public function setCommander(?Commander $commander): self
    {
        $this->commander = $commander;

        // set (or unset) the owning side of the relation if necessary
        $newUser = $commander === null ? null : $this;
        if ($newUser !== $commander->getUser()) {
            $commander->setUser($newUser);
        }

        return $this;
    }

    /**
     * @return Collection|ImportQueue[]
     */
    public function getImportQueues(): Collection
    {
        return $this->importQueues;
    }

    public function addImportQueue(ImportQueue $importQueue): self
    {
        if (!$this->importQueues->contains($importQueue)) {
            $this->importQueues[] = $importQueue;
            $importQueue->setUser($this);
        }

        return $this;
    }

    public function removeImportQueue(ImportQueue $importQueue): self
    {
        if ($this->importQueues->contains($importQueue)) {
            $this->importQueues->removeElement($importQueue);
            // set the owning side to null (unless already changed)
            if ($importQueue->getUser() === $this) {
                $importQueue->setUser(null);
            }
        }

        return $this;
    }

    public function getWelcomeMessageFlag(): ?string
    {
        return $this->welcome_message_flag;
    }

    public function setWelcomeMessageFlag(string $welcome_message_flag): self
    {
        $this->welcome_message_flag = $welcome_message_flag;

        return $this;
    }

    public function getStatusComment(): ?string
    {
        return $this->status_comment;
    }

    public function setStatusComment(?string $status_comment): self
    {
        $this->status_comment = $status_comment;

        return $this;
    }

    public function getCustomRank(): ?CustomRank
    {
        return $this->custom_rank;
    }

    public function setCustomRank(?CustomRank $custom_rank): self
    {
        $this->custom_rank = $custom_rank;

        return $this;
    }

    public function getTmpPassword(): ?string
    {
        return $this->tmp_password;
    }

    public function setTmpPassword(?string $tmp_password): self
    {
        $this->tmp_password = $tmp_password;

        return $this;
    }

    /**
     * @return Collection|SessionTracker[]
     */
    public function getSessionTrackers(): Collection
    {
        return $this->sessionTrackers;
    }

    public function addSessionTracker(SessionTracker $sessionTracker): self
    {
        if (!$this->sessionTrackers->contains($sessionTracker)) {
            $this->sessionTrackers[] = $sessionTracker;
            $sessionTracker->setUser($this);
        }

        return $this;
    }

    public function removeSessionTracker(SessionTracker $sessionTracker): self
    {
        if ($this->sessionTrackers->contains($sessionTracker)) {
            $this->sessionTrackers->removeElement($sessionTracker);
            // set the owning side to null (unless already changed)
            if ($sessionTracker->getUser() === $this) {
                $sessionTracker->setUser(null);
            }
        }

        return $this;
    }

}
