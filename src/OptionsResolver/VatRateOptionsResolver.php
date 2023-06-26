<?php

namespace App\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolver;

class VatRateOptionsResolver extends OptionsResolver
{
    public function configureRate(bool $isRequired = true): self {
        $this->setDefined("rate")->setAllowedTypes("rate", ["integer", "float"]);

        if($isRequired) {
            $this->setRequired("rate");
        }

        return $this;
    }

    public function configureCountry(bool $isRequired = true): self {
        $this->setDefined("country")->setAllowedTypes("country", "integer");
        if($isRequired) {
            $this->setRequired("country");
        }

        return $this;
    }

    public function configureProduct(bool $isRequired = true): self {
        $this->setDefined("product")->setAllowedTypes("product", "integer");
        if($isRequired) {
            $this->setRequired("product");
        }

        return $this;
    }
}
