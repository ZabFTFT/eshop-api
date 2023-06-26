<?php

namespace App\Entity;

use App\Repository\VatRateRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;


#[ORM\Entity(repositoryClass: VatRateRepository::class)]
#[UniqueEntity(fields: ['product', 'country'], message: 'The VAT rate for this product and country combination already exists.')]
class VatRate
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 5, scale: 2)]
    #[Assert\NotBlank]
    #[Assert\GreaterThanOrEqual(0)]
    #[Assert\LessThan(20)]
    private ?string $rate = null;

    #[ORM\ManyToOne(inversedBy: 'vatRate')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotBlank]
    private ?Product $product = null;

    #[ORM\ManyToOne(inversedBy: 'vatRates')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotBlank]
    private ?Country $country = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRate(): ?string
    {
        return $this->rate;
    }

    public function setRate(string $rate): static
    {
        $this->rate = $rate;

        return $this;
    }

    // public function getCountry(): ?Country
    // {
    //     return $this->country;
    // }

    // public function setCountry(?Country $country): static
    // {
    //     $this->country = $country;

    //     return $this;
    // }

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function setProduct(?Product $product): static
    {
        $this->product = $product;

        return $this;
    }

    public function getCountry(): ?Country
    {
        return $this->country;
    }

    public function setCountry(?Country $country): static
    {
        $this->country = $country;

        return $this;
    }
}
