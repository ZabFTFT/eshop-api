<?php

namespace App\Entity;

use App\Repository\ProductRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


#[ORM\Entity(repositoryClass: ProductRepository::class)]
class Product
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, unique: true)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank]
    private ?string $description = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    #[Assert\NotBlank]
    #[Assert\GreaterThan(value: 0)]
    #[Assert\LessThan(value: 99999999)]
    private ?string $price = null;

    #[ORM\OneToMany(mappedBy: 'product', targetEntity: VatRate::class, orphanRemoval: true)]
    #[ORM\JoinColumn(nullable: true)]
    private Collection $vatRate;

    public function __construct()
    {
        $this->vatRate = new ArrayCollection();
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getPrice(): ?string
    {
        return $this->price;
    }

    public function setPrice(string $price): static
    {
        $this->price = $price;

        return $this;
    }

    /**
     * @return Collection<int, VatRate>
     */
    public function getVatRate(): Collection
    {
        return $this->vatRate;
    }

    public function addVatRate(VatRate $vatRate): static
    {
        if (!$this->vatRate->contains($vatRate)) {
            $this->vatRate->add($vatRate);
            $vatRate->setProduct($this);
        }

        return $this;
    }

    public function removeVatRate(VatRate $vatRate): static
    {
        if ($this->vatRate->removeElement($vatRate)) {
            // set the owning side to null (unless already changed)
            if ($vatRate->getProduct() === $this) {
                $vatRate->setProduct(null);
            }
        }

        return $this;
    }
}
