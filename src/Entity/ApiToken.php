<?php

namespace App\Entity;

use App\Entity\Duck;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\ApiTokenRepository;

/**
 * @ORM\Entity(repositoryClass=ApiTokenRepository::class)
 */
class ApiToken
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $token;

    /**
     * @ORM\Column(type="datetime_immutable")
     */
    private $expiresAt;

    /**
     * @ORM\ManyToOne(targetEntity=Duck::class, inversedBy="apiTokens")
     * @ORM\JoinColumn(nullable=false)
     */
    private $duck;

    public function __construct(Duck $duck)
    {
        $this->token = bin2hex(random_bytes(60));
        $this->duck = $duck;
        $this->expiresAt = new \DateTimeImmutable('+1 hour');
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function getExpiresAt(): ?\DateTimeImmutable
    {
        return $this->expiresAt;
    }

    public function isExpired(): bool
    {
        return $this->getExpiresAt() <= new \DateTime();
    }

    public function renewExpiresAt()
    {
        $this->expiresAt = new \DateTime('+1 hour');
    }

    public function getDuck(): ?Duck
    {
        return $this->duck;
    }

    public function setDuck(?Duck $duck): self
    {
        $this->duck = $duck;

        return $this;
    }
}
