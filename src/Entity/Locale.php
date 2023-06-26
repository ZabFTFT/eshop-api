<?php

namespace App\Entity;

use App\Repository\LocaleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


#[ORM\Entity(repositoryClass: LocaleRepository::class)]
class Locale
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, unique:true)]
    #[Assert\NotBlank]
    private ?string $name = null;

    #[ORM\Column(length:255, unique:true)]
    #[Assert\NotBlank]
    private ?string $ISO = null;

    #[ORM\OneToMany(mappedBy: 'locale', targetEntity: Country::class)]
    private Collection $countries;

    public function __construct()
    {
        $this->countries = new ArrayCollection();
        
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getISO(): ?string
    {
        return $this->ISO;
    }

    public function setISO(string $ISO): static
    {
        $this->ISO = $ISO;

        return $this;
    }

    /**
     * @return Collection<int, Country>
     */
    public function getCountries(): Collection
    {
        return $this->countries;
    }

    public function addCountry(Country $country): static
    {
        if (!$this->countries->contains($country)) {
            $this->countries->add($country);
            $country->setLocale($this);
        }

        return $this;
    }

    // public function removeCountry(Country $country): static
    // {
    //     if ($this->countries->removeElement($country)) {
    //         // set the owning side to null (unless already changed)
    //         if ($country->getLocale() === $this) {
    //             $country->setLocale(null);
    //         }
    //     }

    //     return $this;
    // }
}
