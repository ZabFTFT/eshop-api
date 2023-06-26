<?php

namespace App\Controller;

use App\Entity\VatRate;
use App\Entity\Product;
use App\Entity\Country;
use InvalidArgumentException;

use App\OptionsResolver\VatRateOptionsResolver;
use App\Repository\VatRateRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route("/api", name:"api_")]
class VatRateController extends AbstractController
{
    #[Route("/vat_rates", name:"get_vat_rates", methods:["GET"])]
    public function getVatRates(ManagerRegistry $doctrine): JsonResponse
    {
        $vatRates = $doctrine
            ->getRepository(VatRate::class)
            ->findAll();
        
        return $this->json($vatRates);
    }

    #[Route("/vat_rates/{id}", name: "get_vat_rate", methods:["GET"])]
    public function getVatRate(VatRate $vatRate): JsonResponse
    {
        return $this->json($vatRate);
    }

    #[Route("/vat_rates", name:"create_vat_rate", methods:["POST"])]
    public function createVatRate(Request $request, ManagerRegistry $doctrine, VatRateOptionsResolver $vatRateOptionsResolver, ValidatorInterface $validator): JsonResponse
    {
        $entityManager = $doctrine->getManager();
        
        $requestBody = json_decode($request->getContent(), true);

        $fields = $vatRateOptionsResolver
            ->configureRate(true)
            ->configureProduct(true)
            ->configureCountry(true)
            ->resolve($requestBody);

        $vatRate = new VatRate();

        $country = $entityManager->getRepository(Country::class)->find($fields["country"]);
        $product = $entityManager->getRepository(Product::class)->find($fields["product"]);

        $vatRate->setCountry($country);

        $vatRate->setRate($fields["rate"]);

        $vatRate->setProduct($product);

        $errors = $validator->validate($vatRate);
            if(count($errors) > 0) {
                throw new InvalidArgumentException((string) $errors);
            }


        $entityManager->persist($vatRate);
        $entityManager->flush();
        
        return $this->json($vatRate);
    }

    #[Route("/vat_rates/{id}", name:"update_vat_rate", methods:["PUT", "PATCH"])]
    public function updateVatRate(ManagerRegistry $doctrine, Request $request, int $id, VatRateOptionsResolver $vatRateOptionsResolver, ValidatorInterface $validator): JsonResponse
    {
        $entityManager = $doctrine->getManager();
        $requestBody = json_decode($request->getContent(), true);
        $isPutMethod = $request->getMethod() === "PUT";

        $vatRate = $entityManager->getRepository(VatRate::class)->find($id);

        $fields = $vatRateOptionsResolver
            ->configureRate($isPutMethod)
            ->configureProduct($isPutMethod)
            ->configureCountry($isPutMethod)
            ->resolve($requestBody);

        foreach($fields as $field => $value) {
            switch($field) {
                case "rate":
                    $vatRate->setRate($value);
                    break;
                case "product":
                    $vatRate->setProduct($entityManager->getRepository(Product::class)->find($fields["product"]));
                    break;
                case "country":
                    $vatRate->setCountry($entityManager->getRepository(Country::class)->find($fields["country"]));
                    break;
            }
        }

        $errors = $validator->validate($vatRate);
            if(count($errors) > 0) {
                throw new InvalidArgumentException((string) $errors);
            }

        $entityManager->flush();

        return $this->json($vatRate);
    }

    #[Route("/vat_rates/{id}", name: "delete_vat_rate", methods:["DELETE"])]
    public function deleteVatRate(VatRate $vatRate, VatRateRepository $vatRateRepository)
    {
        $vatRateRepository->remove($vatRate, true);
        return $this->json(null, Response::HTTP_NO_CONTENT);
    }
}   

