<?php

namespace App\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LocaleOptionsResolver extends OptionsResolver
{
    public function configureName(bool $isRequired = true): self {
        $this->setDefined("name")->setAllowedTypes("name", "string");

        if($isRequired) {
            $this->setRequired("name");
        }

        return $this;
    }

    public function configureISO(bool $isRequired = true): self {
        $this->setDefined("ISO")->setAllowedTypes("ISO", "string");
        if($isRequired) {
            $this->setRequired("ISO");
        }

        return $this;
    }
}
