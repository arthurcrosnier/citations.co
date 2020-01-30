<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
/**
 * CitationV2
 *
 * @ORM\Table(name="citation_v2", uniqueConstraints={@ORM\UniqueConstraint(name="UNIQ_1F5870E8F47645AE", columns={"url"})}, indexes={@ORM\Index(name="IDX_1F5870E860BB6FE6", columns={"auteur_id"})})
 * @ORM\Entity(repositoryClass="App\Repository\CitationV2Repository")
 */
class CitationV2
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
     * @var int
     *
     * @ORM\Column(name="language", type="integer", nullable=false)
     */
    private $language;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=false)
     */
    private $description;

    /**
     * @var boolean
     *
     * @ORM\Column(name="citation_du_jour", type="boolean", nullable=false)
     */
    private $citation_du_jour;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_citation_du_jour", type="datetime", nullable=true)
     */
    private $date_citation_du_jour;

    /**
     * @var boolean
     *
     * @ORM\Column(name="citation_en_avant", type="boolean", nullable=false)
     */
    private $citation_en_avant;

    /**
     * @var int
     *
     * @ORM\Column(name="auteurChecked", type="integer", nullable=true)
     */
    private $auteur_checked;

    /**
     * @var string
     *
     * @ORM\Column(name="url", type="string", length=100, nullable=false)
     */
    private $url;

    /**
     * @var string
     *
     * @ORM\Column(name="vraiAuteur", type="string", length=100, nullable=true)
     */
    private $vrai_auteur;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="upload", type="datetime", nullable=false)
     */
    private $upload;

    /**
     * @var int|null
     *
     * @ORM\Column(name="old", type="integer", nullable=true, options={"default"="NULL"})
     */
    private $old = 'NULL';

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Auteur", inversedBy="citations", cascade={"persist"})
     */
    private $auteur;

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\ManyToMany(targetEntity="Tag", inversedBy="citation")
     * @ORM\JoinTable(name="citation_tag",
     *   joinColumns={
     *     @ORM\JoinColumn(name="citation_id", referencedColumnName="id")
     *   },
     *   inverseJoinColumns={
     *     @ORM\JoinColumn(name="tag_id", referencedColumnName="id")
     *   }
     * )
     */
    private $tag;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\LikeCitationCelebre", mappedBy="citation")
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
        $this->tag = new \Doctrine\Common\Collections\ArrayCollection();
        $this->likes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLanguage(): ?int
    {
        return $this->language;
    }

    public function setLanguage(int $language): self
    {
        $this->language = $language;

        return $this;
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

    public function getOld(): ?int
    {
        return $this->old;
    }

    public function setOld(?int $old): self
    {
        $this->old = $old;

        return $this;
    }

    public function getAuteur(): ?Auteur
    {
        return $this->auteur;
    }

    public function setAuteur(?Auteur $auteur): self
    {
        $this->auteur = $auteur;

        return $this;
    }

    /**
     * @return Collection|Tag[]
     */
    public function getTag(): Collection
    {
        return $this->tag;
    }

    public function addTag(Tag $tag): self
    {
        if (!$this->tag->contains($tag)) {
            $this->tag[] = $tag;
        }

        return $this;
    }

    public function removeTag(Tag $tag): self
    {
        if ($this->tag->contains($tag)) {
            $this->tag->removeElement($tag);
        }

        return $this;
    }

    public function hascitation_du_jour(): ?bool
    {
        return $this->citation_du_jour;
    }

    public function getCitationDuJour(): ?bool
    {
        return $this->citation_du_jour;
    }

    public function setCitationDuJour(?bool $citation_du_jour): self
    {
        $this->citation_du_jour = $citation_du_jour;

        return $this;
    }

    public function getCitationEnAvant(): ?int
    {
        return $this->citation_en_avant;
    }

    public function setCitationEnAvant(?int $citation_en_avant): self
    {
        $this->citation_en_avant = $citation_en_avant;

        return $this;
    }

    public function getAuteurChecked(): ?int
    {
        return $this->auteur_checked;
    }

    public function setAuteurChecked(?int $auteur_checked): self
    {
        $this->auteur_checked = $auteur_checked;

        return $this;
    }

    public function getVraiAuteur(): ?string
    {
        return $this->vrai_auteur;
    }

    public function setVraiAuteur(?string $vraiauteur): self
    {
        $this->vrai_auteur = $vraiauteur;

        return $this;
    }

    public function getdate_citation_du_jour(): ?\DateTimeInterface
    {
        return $this->date_citation_du_jour;
    }

    public function setDateCitationDuJour(\DateTimeInterface $date_citation_du_jour): self
    {
        if ($date_citation_du_jour != null)
            $this->date_citation_du_jour = $date_citation_du_jour;

        return $this;
    }

    /**
     * @return Collection|Like[]
     */
    public function getlikes(): Collection
    {
        return $this->likes;
    }

    public function addLikes(LikeCitationCelebre $likes): self
    {
        if (!$this->likes->contains($likes)) {
            $this->likes[] = $likes;
            $likes->setCitation($this);
        }

        return $this;
    }

    public function removeLikes(LikeCitationCelebre $likes): self
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


