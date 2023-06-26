<?php

namespace App\Serializer;
use App\Entity\VatRate;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class CircularReferenceHandler
{
    private $urlGenerator;
    public function __construct(UrlGeneratorInterface $urlGeneratorInterface)
    {
        $this->urlGenerator = $urlGeneratorInterface;
    }

    public function __invoke($object)
    {
        switch($object) {
            case $object instanceof Country:
                return $this->urlGenerator->generate("api_get_country", ['country' => $object->getId()]);
            case $object instanceof Product:
                return $this->urlGenerator->generate("api_get_product", ['product' => $object->getId()]);
            case $object instanceof VatRate:
                return $this->urlGenerator->generate("api_get_vat_rate", ['id' => $object->getId()]);
        }
    }
}