<?php

namespace App\Controller;

use App\Entity\Product;
use App\Entity\VatRate;
use App\Entity\Locale;
use App\Entity\Country;


use App\OptionsResolver\ProductOptionsResolver;
use App\Repository\ProductRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route("/api", name: "api_")]
class ProductController extends AbstractController
{
    #[Route("/products", name:"get_products", methods:["GET"])]
    public function getVatRates(ManagerRegistry $doctrine): JsonResponse
    {
        $products = $doctrine
            ->getRepository(Product::class)
            ->findAll();

        return $this->json($products);
    }

    #[Route("/products/{id}", name: "get_product", methods:["GET"])]
    public function getCountry(Product $product, Request $request, ManagerRegistry $doctrine): JsonResponse
    {
        $entityManager = $doctrine->getManager();

        $localeIsoQueryParam = strtolower(rtrim($request->query->get("locale")));
        $data = [
            "name" => $product->getName(),
            "description" => $product->getDescription(),
            "excluded_VAT_price" => $product->getPrice(),
        ];
        if ($localeIsoQueryParam) {
            $locale = $entityManager->getRepository(Locale::class)->findOneBy(["ISO" => $localeIsoQueryParam]);
            if ($locale) {
                $country = $entityManager->getRepository(Country::class)->findOneBy(["locale" => $locale]);
                $vatRate = $entityManager->getRepository(VatRate::class)->findOneBy(["country" => $country, "product" => $product]);
                if ($vatRate) {
                    $vatRateValue = $vatRate->getRate() / 100;
                    $includedVatPrice = $product->getPrice() * (1 + $vatRateValue);
                    $data["included_VAT_price"] = number_format($includedVatPrice, 2, ".", "");
                }
            }
        }

        return $this->json($data);
    }


    #[Route("/products", name:"create_product", methods:["POST"])]
    public function createProduct(Request $request, ManagerRegistry $doctrine, ProductOptionsResolver $productOptionsResolver, ValidatorInterface $validator ): JsonResponse
    {
        $entityManager = $doctrine->getManager();
        
        $requestBody = json_decode($request->getContent(), true);

        $fields = $productOptionsResolver
            ->configureName(true)
            ->configureDescription(true)
            ->configurePrice(true)
            ->resolve($requestBody);
        
        $existingProductName = $entityManager->getRepository(Product::class)->findOneBy(['name' => $fields['name']]);
        if ($existingProductName !== null) {
            return $this->json(['errors' => 'The name must be unique'], Response::HTTP_BAD_REQUEST);
        }

        $product = new Product();
        $product->setName($fields["name"]);
        $product->setDescription($fields["description"]);
        $product->setPrice($fields["price"]);

        $errors = $validator->validate($product);
        if (count($errors) > 0) {
            return $this->json(['errors' => (string) $errors], Response::HTTP_BAD_REQUEST);
        }


        $entityManager->persist($product);
        $entityManager->flush();

        return $this->json($product);
    }

    #[Route("/products/{id}", name:"update_product", methods:["PUT", "PATCH"])]
    public function updateCountry(ManagerRegistry $doctrine, Request $request, int $id, ProductOptionsResolver $productOptionsResolver, ValidatorInterface $validator): JsonResponse
    {
        $entityManager = $doctrine->getManager();
        $requestBody = json_decode($request->getContent(), true);
        $isPutMethod = $request->getMethod() === "PUT";

        $product = $entityManager->getRepository(Product::class)->find($id);

        $fields = $productOptionsResolver
            ->configureName($isPutMethod)
            ->configureDescription($isPutMethod)
            ->configurePrice($isPutMethod)
            ->resolve($requestBody);

        foreach($fields as $field => $value) {
            switch($field) {
                case "name":
                    $existingProductName = $entityManager->getRepository(Country::class)->findOneBy(['name' => $fields['name']]);
                    if ($existingProductName !== null) {
                        return $this->json(['errors' => 'The name must be unique'], Response::HTTP_BAD_REQUEST);
                    }
                    $product->setName($value);
                    break;
                case "description":
                    $product->setDescription($value);
                    break;
                case "price":
                    $product->setPrice($value);
            }
        }

        $errors = $validator->validate($product);
        if (count($errors) > 0) {
            return $this->json(['errors' => (string) $errors], Response::HTTP_BAD_REQUEST);
        }

        $entityManager->flush();

        return $this->json($product);
    }

    #[Route("/products/{id}", name: "delete_products", methods:["DELETE"])]
    public function deleteProduct(Product $product, ProductRepository $productRepository)
    {
        $productRepository->remove($product, true);
        return $this->json(null, Response::HTTP_NO_CONTENT);
    }
}
