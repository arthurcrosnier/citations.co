<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Tag
 *
 * @ORM\Table(name="tag")
 * @ORM\Entity(repositoryClass="App\Repository\TagRepository")
 */
class Tag
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=50, nullable=false)
     */
    private $name;


    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\ManyToMany(targetEntity="CitationV2", mappedBy="tag")
     */
    private $citation;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->citation = new \Doctrine\Common\Collections\ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    /**
     * @return Collection|CitationV2[]
     */
    public function getCitation(): Collection
    {
        return $this->citation;
    }

    public function addCitation(CitationV2 $citation): self
    {
        if (!$this->citation->contains($citation)) {
            $this->citation[] = $citation;
            $citation->addTag($this);
        }

        return $this;
    }

    public function removeCitation(CitationV2 $citation): self
    {
        if ($this->citation->contains($citation)) {
            $this->citation->removeElement($citation);
            $citation->removeTag($this);
        }

        return $this;
    }

}
