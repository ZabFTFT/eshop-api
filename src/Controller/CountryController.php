<?php

namespace App\Controller;

use App\Entity\Country;
use App\Entity\Locale;
use App\OptionsResolver\CountryOptionsResolver;
use App\Repository\CountryRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route("/api", name: "api_")]
class CountryController extends AbstractController
{
    #[Route("/countries", name: "get_countries", methods: ["GET"])]
    public function getCountries(ManagerRegistry $doctrine): JsonResponse
    {
        $countries = $doctrine
            ->getRepository(Country::class)
            ->findAll();

        return $this->json($countries);
    }

    #[Route("/countries/{id}", name: "get_country", methods: ["GET"])]
    public function getCountry(Country $country): JsonResponse
    {
        return $this->json($country);
    }


    #[Route("/countries", name: "create_country", methods: ["POST"])]
    public function createCountry(
        Request $request,
        ManagerRegistry $doctrine,
        CountryOptionsResolver $countryOptionsResolver,
        ValidatorInterface $validator
    ): JsonResponse {
        $entityManager = $doctrine->getManager();

        $requestBody = json_decode($request->getContent(), true);
        $fields = $countryOptionsResolver
            ->configureName(true)
            ->configureLocale(true)
            ->resolve($requestBody);

        $existingCountryName = $entityManager->getRepository(Country::class)->findOneBy(['name' => $fields['name']]);
        if ($existingCountryName !== null) {
            return $this->json(['errors' => 'The name must be unique'], Response::HTTP_BAD_REQUEST);
        }

        $country = new Country();
        $country->setName($fields["name"]);
        $locale = $entityManager->getRepository(Locale::class)->find($fields["locale"]);
        $country->setLocale($locale);

        $errors = $validator->validate($country);
        if (count($errors) > 0) {
            return $this->json(['errors' => (string) $errors], Response::HTTP_BAD_REQUEST);
        }

        $entityManager->persist($country);
        $entityManager->flush();

        return $this->json($country);
    }

    #[Route("/countries/{id}", name: "update_country", methods: ["PUT", "PATCH"])]
    public function updateCountry(
        ManagerRegistry $doctrine,
        Request $request,
        int $id,
        CountryOptionsResolver $countryOptionsResolver,
        ValidatorInterface $validator
    ): JsonResponse {
        $entityManager = $doctrine->getManager();
        $requestBody = json_decode($request->getContent(), true);
        $isPutMethod = $request->getMethod() === "PUT";

        $country = $entityManager->getRepository(Country::class)->find($id);

        $fields = $countryOptionsResolver
            ->configureName($isPutMethod)
            ->configureLocale($isPutMethod)
            ->resolve($requestBody);

        foreach ($fields as $field => $value) {
            switch ($field) {
                case "name":
                    $existingCountryName = $entityManager->getRepository(Country::class)->findOneBy(['name' => $fields['name']]);
                    if ($existingCountryName !== null) {
                        return $this->json(['errors' => 'The name must be unique'], Response::HTTP_BAD_REQUEST);
                    }
                    $country->setName($value);
                    break;
                case "locale":
                    $country->setLocale($entityManager->getRepository(Locale::class)->find($fields["locale"]));
                    break;
            }
        }

        $errors = $validator->validate($country);
        if (count($errors) > 0) {
            return $this->json(['errors' => (string) $errors], Response::HTTP_BAD_REQUEST);
        }

        $entityManager->flush();

        return $this->json($country);
    }

    #[Route("/countries/{id}", name: "delete_country", methods: ["DELETE"])]
    public function deleteCountry(Country $country, CountryRepository $countryRepository): JsonResponse
    {
        $countryRepository->remove($country, true);
        return $this->json(null, Response::HTTP_NO_CONTENT);
    }
}
