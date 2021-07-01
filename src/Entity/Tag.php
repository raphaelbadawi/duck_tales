<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\TagRepository;
use ApiPlatform\Core\Annotation\ApiResource;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=TagRepository::class)
 * @ApiResource(
 *  normalizationContext={"groups"={"tag:read"}},
 *  denormalizationContext={"groups"={"tag:write"}},
 *  paginationEnabled=false
 * )
 */
class Tag
{
    public function __toString() {
        return $this->getContent();
    }
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    #[Groups(['tag:read'])]
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    #[Groups(['tag:read', 'tag:write'])]
    private $content;

    /**
     * @ORM\ManyToOne(targetEntity=Quack::class, inversedBy="tags")
     * @ORM\JoinColumn(nullable=false)
     */
    private $quack;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getQuack(): ?Quack
    {
        return $this->quack;
    }

    public function setQuack(?Quack $quack): self
    {
        $this->quack = $quack;

        return $this;
    }
}
