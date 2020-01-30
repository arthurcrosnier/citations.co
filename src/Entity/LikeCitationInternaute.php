<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="LikeCitationInternaute")
 * @ORM\Entity(repositoryClass="App\Repository\LikeCitationInternauteRepository")
 */
class LikeCitationInternaute
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\CitationInternaute", inversedBy="likes")
     * @ORM\JoinColumn(nullable=false)
     */
    private $citation;

    /**
     * @var string
     *
     * @ORM\Column(name="ip", type="string", length=30, nullable=false)
     */
    private $ip;

    /**
     * @ORM\Column(type="datetime", nullable=false)
     */
    private $likeDate;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCitation(): ?CitationInternaute
    {
        return $this->citation;
    }

    public function setCitation(?CitationInternaute $citation): self
    {
        $this->citation = $citation;

        return $this;
    }

    public function getIp(): ?string
    {
        return $this->ip;
    }

    public function setIp(string $ip): self
    {
        $this->ip = $ip;

        return $this;
    }

    public function getLikeDate(): ?\DateTimeInterface
    {
        return $this->likeDate;
    }

    public function setLikeDate(?\DateTimeInterface $likeDate): self
    {
        $this->likeDate = $likeDate;

        return $this;
    }
}
