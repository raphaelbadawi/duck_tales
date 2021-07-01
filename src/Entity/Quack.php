<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\QuackRepository;
use Doctrine\Common\Collections\Collection;
use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiSubresource;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=QuackRepository::class)
 * @ApiResource(
 *  normalizationContext={"groups"={"quack:read"}},
 *  denormalizationContext={"groups"={"quack:write"}},
 *  order={"created_at"="DESC"},
 *  paginationEnabled=false
 * )
 */
class Quack
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    #[Groups(['quack:read'])]
    private $id;

    /**
     * @ORM\OneToMany(targetEntity="Quack", mappedBy="parent")
     */
    #[ApiProperty(readableLink: true, writableLink: true)]
    #[ApiSubresource()]
    #[Groups(['quack:read', 'quack:write'])]
    protected $comments;

    /**
     * @ORM\ManyToOne(targetEntity="Quack", inversedBy="comments")
     * @ORM\JoinColumn(name="parent", referencedColumnName="id")
     */
    protected $parent;

    /**
     * @ORM\Column(type="text")
     * @Assert\Length(min=4, minMessage = "Your content must be at least {{ limit }} characters long.", max=280, maxMessage = "Your content must me at less than {{ limit }} characters long.")
     */
    #[Groups(['quack:read', 'quack:write'])]
    private $content;

    /**
     * @ORM\Column(type="datetime_immutable")
     */
    #[Groups(['quack:read'])]
    private $created_at;

    /**
     * @ORM\ManyToOne(targetEntity=Duck::class, inversedBy="quacks")
     */
    #[ApiProperty(readableLink: true, writableLink: true)]
    #[ApiSubresource()]
    #[Groups(['quack:read', 'quack:write'])]
    private $duck;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    #[Groups(['quack:read', 'quack:write'])]
    private $picture;

    /**
     * @ORM\OneToMany(targetEntity=Tag::class, mappedBy="quack", orphanRemoval=true, cascade={"persist"})
     */
    #[ApiProperty(readableLink: true, writableLink: true)]
    #[Groups(['quack:read', 'quack:write'])]
    private $tags;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $oldId;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isOld;

    /**
     * @ORM\ManyToMany(targetEntity=Duck::class, mappedBy="likes", orphanRemoval=true, cascade={"persist"})
     */
    private $ducks;

    public function __construct()
    {
        $this->tags = new ArrayCollection();
        $this->children = new ArrayCollection();
        $this->ducks = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getParent(): ?self
    {
        return $this->parent;
    }

    public function setParent(Quack $parent)
    {
        $this->parent = $parent;
    }

    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Quack $comment)
    {
        $this->comments[] = $comment;
        $comment->setParent($this);
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

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeImmutable $created_at): self
    {
        $this->created_at = $created_at;

        return $this;
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

    public function getPicture(): ?string
    {
        return $this->picture;
    }

    public function setPicture(?string $picture): self
    {
        $this->picture = $picture;

        return $this;
    }

    /**
     * @return Collection|Tag[]
     */
    public function getTags(): Collection
    {
        return $this->tags;
    }

    public function addTag(Tag $tag): self
    {
        if (!$this->tags->contains($tag)) {
            $this->tags[] = $tag;
            $tag->setQuack($this);
        }

        return $this;
    }

    public function removeTag(Tag $tag): self
    {
        if ($this->tags->removeElement($tag)) {
            // set the owning side to null (unless already changed)
            if ($tag->getQuack() === $this) {
                $tag->setQuack(null);
            }
        }

        return $this;
    }

    public function getOldId(): ?int
    {
        return $this->oldId;
    }

    public function setOldId(?int $oldId): self
    {
        $this->oldId = $oldId;

        return $this;
    }

    public function getIsOld(): ?bool
    {
        return $this->isOld;
    }

    public function setIsOld(?bool $isOld): self
    {
        $this->isOld = $isOld;

        return $this;
    }

    public function getDucks(): Collection
    {
        return $this->ducks;
    }

    public function addDuck(Duck $duck): self
    {
        if (!$this->ducks->contains($duck)) {
            $this->ducks[] = $duck;
            $duck->addLike($this);
        }

        return $this;
    }

    public function removeUser(Duck $duck): self
    {
        if ($this->ducks->contains($duck)) {
            $this->ducks->removeElement($duck);
            $duck->removeLike($this);
        }

        return $this;
    }
}
