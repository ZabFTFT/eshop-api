<?php

namespace App\Entity;

use App\Repository\CountryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


#[ORM\Entity(repositoryClass: CountryRepository::class)]
class Country
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, unique: true)]
    #[Assert\NotBlank]
    private ?string $name = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotBlank]
    private ?Locale $locale = null;

    #[ORM\OneToMany(mappedBy: 'country', targetEntity: VatRate::class, orphanRemoval: true)]
    private Collection $vatRates;

    public function __construct()
    {
        $this->vatRates = new ArrayCollection();
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

    public function getLocale(): ?Locale
    {
        return $this->locale;
    }

    public function setLocale(Locale $locale): static
    {
        $this->locale = $locale;

        return $this;
    }

    /**
     * @return Collection<int, VatRate>
     */
    public function getVatRates(): Collection
    {
        return $this->vatRates;
    }

    public function addVatRate(VatRate $vatRate): static
    {
        if (!$this->vatRates->contains($vatRate)) {
            $this->vatRates->add($vatRate);
            $vatRate->setCountry($this);
        }

        return $this;
    }

    public function removeVatRate(VatRate $vatRate): static
    {
        if ($this->vatRates->removeElement($vatRate)) {
            // set the owning side to null (unless already changed)
            if ($vatRate->getCountry() === $this) {
                $vatRate->setCountry(null);
            }
        }

        return $this;
    }
}
