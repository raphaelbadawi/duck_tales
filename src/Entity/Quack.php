<?php

namespace App\Entity;

use App\Repository\QuackRepository;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=QuackRepository::class)
 */
class Quack
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\OneToMany(targetEntity="Quack", mappedBy="parent")
     */
    protected $comments;

    /**
     * @ORM\ManyToOne(targetEntity="Quack", inversedBy="comments")
     * @ORM\JoinColumn(name="parent", referencedColumnName="id")
     */
    protected $parent;

    /**
     * @ORM\Column(type="text")
     * @Assert\Length(max=280, maxMessage = "Your content must me at less than {{ limit }} characters long.")
     */
    private $content;

    /**
     * @ORM\Column(type="datetime_immutable")
     */
    private $created_at;

    /**
     * @ORM\ManyToOne(targetEntity=Duck::class, inversedBy="quacks")
     */
    private $duck;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $picture;

    /**
     * @ORM\OneToMany(targetEntity=Tag::class, mappedBy="quack", orphanRemoval=true, cascade={"persist"})
     */
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

    public function getParent(): self
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
