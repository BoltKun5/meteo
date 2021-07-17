<?php

namespace App\Entity;

use App\Repository\NextTripRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=NextTripRepository::class)
 */
class NextTrip
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
    private $Ville1;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getVille1(): ?string
    {
        return $this->Ville1;
    }

    public function setVille1(string $Ville1): self
    {
        $this->Ville1 = $Ville1;

        return $this;
    }

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $Ville2;

    public function getVille2(): ?string
    {
        return $this->Ville2;
    }

    public function setVille2(string $Ville2): self
    {
        $this->Ville2 = $Ville2;

        return $this;
    }
}
