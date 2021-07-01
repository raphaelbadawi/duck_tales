<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\DuckRepository;
use Doctrine\Common\Collections\Collection;
use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

/**
 * @ORM\Entity(repositoryClass=DuckRepository::class)
 * @ApiResource(
 *  normalizationContext={"groups"={"duck:read"}},
 *  order={"created_at"="DESC"},
 *  paginationEnabled=false
 * )
 */
class Duck implements UserInterface, PasswordAuthenticatedUserInterface
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     */
    #[Groups(['duck:read'])]
    private $email;

    /**
     * @ORM\Column(type="json")
     */
    private $roles = [];

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     * @Assert\Length(min=4, minMessage = "Your password must me at least {{ limit }} characters long.")
     */
    private $password;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\Length(min=4, minMessage = "Your first name must me at least {{ limit }} characters long.")
     */
    #[Groups(['duck:read', 'duck:write'])]
    private $firstname;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\Length(min=4, minMessage = "Your last name must me at least {{ limit }} characters long.")
     */
    #[Groups(['duck:read'])]
    private $lastname;

    /**
     * @ORM\Column(type="string", length=255, unique=true),
     * @Assert\Length(min=4, minMessage = "Your duck name must me at least {{ limit }} characters long.")
     */
    #[Groups(['duck:read'])]
    private $duckname;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $googleId;

    /**
     * @ORM\OneToMany(targetEntity=Quack::class, mappedBy="duck")
     */
    private $quacks;

    /**
     * @ORM\ManyToMany(targetEntity=Quack::class, inversedBy="ducks")
     */
    private $likes;

    /**
     * @ORM\OneToMany(targetEntity=ApiToken::class, mappedBy="duck", orphanRemoval=true)
     */
    private $apiTokens;

    public function __construct()
    {
        $this->quacks = new ArrayCollection();
        $this->likes = new ArrayCollection();
        $this->apiTokens = new ArrayCollection();
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
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @deprecated since Symfony 5.3, use getUserIdentifier instead
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

    // helpers for the admin dashboard
    public function hasRoleAdmin()
    {
        return in_array('ROLE_ADMIN', $this->getRoles()) ? 'Yes' : 'No';
    }

    public function setHasRoleAdmin($isAdmin)
    {
        if ('Yes' === $isAdmin && !in_array('ROLE_ADMIN', $this->getRoles())) {
            $this->setRoles(['ROLE_ADMIN']);
        }
        if ('No' === $isAdmin && in_array('ROLE_ADMIN', $this->getRoles())) {
            $this->setRoles([]);
        }
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Returning a salt is only needed, if you are not using a modern
     * hashing algorithm (e.g. bcrypt or sodium) in your security.yaml.
     *
     * @see UserInterface
     */
    public function getSalt(): ?string
    {
        return null;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(string $firstname): self
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(string $lastname): self
    {
        $this->lastname = $lastname;

        return $this;
    }

    public function getDuckname(): ?string
    {
        return $this->duckname;
    }

    public function setDuckname(string $duckname): self
    {
        $this->duckname = $duckname;

        return $this;
    }

    public function getGoogleId(): ?int
    {
        return $this->googleId;
    }

    public function setGoogleId(?int $googleId): self
    {
        $this->googleId = $googleId;

        return $this;
    }

    /**
     * @return Collection|Quack[]
     */
    public function getQuacks(): Collection
    {
        return $this->quacks;
    }

    public function addQuack(Quack $quack): self
    {
        if (!$this->quacks->contains($quack)) {
            $this->quacks[] = $quack;
            $quack->setDuck($this);
        }

        return $this;
    }

    public function removeQuack(Quack $quack): self
    {
        if ($this->quacks->removeElement($quack)) {
            // set the owning side to null (unless already changed)
            if ($quack->getDuck() === $this) {
                $quack->setDuck(null);
            }
        }

        return $this;
    }

    public function getLikes(): Collection
    {
        return $this->likes;
    }

    public function addLike(Quack $like): self
    {
        if (!$this->likes->contains($like)) {
            $this->likes[] = $like;
        }
        return $this;
    }

    public function removeLike(Quack $like): self
    {
        if ($this->likes->contains($like)) {
            $this->likes->removeElement($like);
        }
        return $this;
    }

    /**
     * @return Collection|ApiToken[]
     */
    public function getApiTokens(): Collection
    {
        return $this->apiTokens;
    }

    public function addApiToken(ApiToken $apiToken): self
    {
        if (!$this->apiTokens->contains($apiToken)) {
            $this->apiTokens[] = $apiToken;
            $apiToken->setDuck($this);
        }

        return $this;
    }

    public function removeApiToken(ApiToken $apiToken): self
    {
        if ($this->apiTokens->removeElement($apiToken)) {
            // set the owning side to null (unless already changed)
            if ($apiToken->getDuck() === $this) {
                $apiToken->setDuck(null);
            }
        }

        return $this;
    }
}
