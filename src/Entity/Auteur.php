<?php

namespace App\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Auteur
 *
 * @ORM\Table(name="auteur")
 * @ORM\Entity(repositoryClass="App\Repository\AuteurRepository")
 */
class Auteur
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
     * @ORM\Column(name="name", type="string", length=240, nullable=false)
     */
    private $name;

    /**
     * @var int
     *
     * @ORM\Column(name="type", type="integer", nullable=false)
     */
    private $type;

    /**
     * @var string
     *
     * @ORM\Column(name="bio", type="text", length=10000, nullable=false)
     */
    private $bio;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="upload", type="datetime", nullable=false)
     */
    private $upload;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\CitationV2", mappedBy="auteur")
     */
    private $citations;

    public function __construct()
    {
        $this->citations = new ArrayCollection();
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

    public function getBio(): ?string
    {
        if ($this->bio == null)
            $this->bio = "";
        return $this->bio;
    }

    public function setBio(string $bio): self
    {
        $this->bio = $bio;

        return $this;
    }

    public function getUpload(): ?\DateTimeInterface
    {
        if ($this->upload == null)
            $this->upload = new \DateTime;
        return $this->upload;
    }

    public function setUpload(\DateTimeInterface $upload): self
    {
        $this->upload = $upload;

        return $this;
    }

    public function getType(): ?int
    {;
        return $this->type;
    }

    public function setType(int $type): self
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return Collection|CitationV2[]
     */
    public function getCitations(): Collection
    {
        return $this->citations;
    }

    public function addCitation(CitationV2 $citation): self
    {
        if (!$this->citations->contains($citation)) {
            $this->citations[] = $citation;
            $citation->setAuteur($this);
        }

        return $this;
    }

    public function removeCitation(CitationV2 $citation): self
    {
        if ($this->citations->contains($citation)) {
            $this->citations->removeElement($citation);
            // set the owning side to null (unless already changed)
            if ($citation->getAuteur() === $this) {
                $citation->setAuteur(null);
            }
        }

        return $this;
    }
}
