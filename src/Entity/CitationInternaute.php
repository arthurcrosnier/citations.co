<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * CitationInternaute
 *
 * @ORM\Table(name="Citation_internaute", uniqueConstraints={@ORM\UniqueConstraint(name="UNIQ_1F5870E8F47645AE", columns={"url"})}, indexes={@ORM\Index(name="IDX_1F5870E860BB6FE6", columns={"id"})})
 * @ORM\Entity(repositoryClass="App\Repository\CitationInternauteRepository")
 */
class CitationInternaute
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
     * @ORM\Column(name="description", type="string", length=255, nullable=false)
     */
    private $description;

    /**
     * @var string
     *
     * @ORM\Column(name="url", type="string", length=100, nullable=false)
     */
    private $url;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="upload", type="datetime", nullable=false)
     */
    private $upload;

    /**
     * @var string
     *
     * @ORM\Column(name="auteur", type="string", length=100, nullable=false)
     */
    private $auteur;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="citations", cascade={"persist"})
     */
    private $userAjout;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\LikeCitationInternaute", mappedBy="citation")
     */
    private $likes;

    /**
     * @var int
     *
     * @ORM\Column(name="countLikes", type="integer", nullable=false, options={"default" : 0})
     */
    private $count_likes;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->likes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(string $url): self
    {
        $this->url = $url;

        return $this;
    }

    public function getUpload(): ?\DateTimeInterface
    {
        return $this->upload;
    }

    public function setUpload(\DateTimeInterface $upload): self
    {
        if ($upload != null)
            $this->upload = $upload;
        else if ($this->getUpload() == null)
            $this->upload = new \DateTime;

        return $this;
    }

    public function getUserAjout(): ?User
    {
        return $this->userAjout;
    }

    public function setUserAjout(?User $user): self
    {
        $this->userAjout = $user;

        return $this;
    }

    public function getAuteur(): ?string
    {
        return $this->auteur;
    }

    public function setAuteur(string $auteur): self
    {
        $this->auteur = $auteur;

        return $this;
    }

    /**
     * @return Collection|Like[]
     */
    public function getlikes(): Collection
    {
        return $this->likes;
    }

    public function addLikes(LikeCitationInternaute $likes): self
    {
        if (!$this->likes->contains($likes)) {
            $this->likes[] = $likes;
            $likes->setCitation($this);
        }

        return $this;
    }

    public function removeLikes(LikeCitationInternaute $likes): self
    {
        if ($this->likes->contains($likes)) {
            $this->likes->removeElement($likes);
            // set the owning side to null (unless already changed)
            if ($likes->getCitation() === $this) {
                $likes->setCitation(null);
            }
        }

        return $this;
    }

    public function getCountLikes(): ?int
    {
        return $this->count_likes;
    }

    public function setCountLikes(int $count_likes): self
    {
        $this->count_likes = $count_likes;

        return $this;
    }
}


