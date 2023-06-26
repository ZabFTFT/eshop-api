<?php

namespace App\Controller;

use App\Entity\Locale;
use App\OptionsResolver\LocaleOptionsResolver;
use App\Repository\LocaleRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route("/api", name: "api_")]
class LocaleController extends AbstractController
{
    #[Route("/locales", name: "get_locales", methods: ["GET"])]
    public function getLocales(ManagerRegistry $doctrine): JsonResponse
    {
        $locales = $doctrine
            ->getRepository(Locale::class)
            ->findAll();

        return $this->json($locales);
    }


    #[Route("/locales/{id}", name: "get_locale", methods: ["GET"])]
    public function getLocale(Locale $locale): JsonResponse
    {
        return $this->json($locale);
    }

    #[Route("/locales", name: "create_locale", methods: ["POST"])]
    public function createLocale(
        Request $request,
        ManagerRegistry $doctrine,
        LocaleOptionsResolver $localeOptionsResolver,
        ValidatorInterface $validator
    ): JsonResponse {
        $entityManager = $doctrine->getManager();

        $requestBody = json_decode($request->getContent(), true);

        if (!isset($requestBody['name']) || !isset($requestBody['ISO'])) {
            return $this->json(['errors' => 'name and ISO fields are required'], Response::HTTP_BAD_REQUEST);
        }

        $fields = $localeOptionsResolver
            ->configureName(true)
            ->configureISO(true)
            ->resolve($requestBody);

        $existingLocaleName = $entityManager->getRepository(Locale::class)->findOneBy(['name' => $fields['name']]);
        if ($existingLocaleName !== null) {
            return $this->json(['errors' => 'The name must be unique'], Response::HTTP_BAD_REQUEST);
        }

        $existingISO = $entityManager->getRepository(Locale::class)->findOneBy(['ISO' => $fields['ISO']]);
        if ($existingISO !== null) {
            return $this->json(['errors' => 'The ISO must be unique'], Response::HTTP_BAD_REQUEST);
        }

        $locale = new Locale();
        $locale->setName($fields["name"]);
        $locale->setISO($fields["ISO"]);

        $errors = $validator->validate($locale);

        $errors = $validator->validate($locale);
        if (count($errors) > 0) {
            return $this->json(['errors' => (array) $errors], Response::HTTP_BAD_REQUEST);
        }

        $entityManager->persist($locale);
        $entityManager->flush();

        return $this->json($locale);
    }

    #[Route("/locales/{id}", name: "update_locale", methods: ["PUT", "PATCH"])]
    public function updateLocale(
        ManagerRegistry $doctrine,
        Request $request,
        int $id,
        LocaleOptionsResolver $localeOptionsResolver,
        ValidatorInterface $validator
    ): JsonResponse {
        $entityManager = $doctrine->getManager();
        $requestBody = json_decode($request->getContent(), true);
        $isPutMethod = $request->getMethod() === "PUT";

        $locale = $entityManager->getRepository(Locale::class)->find($id);

        $fields = $localeOptionsResolver
            ->configureName($isPutMethod)
            ->configureISO($isPutMethod)
            ->resolve($requestBody);
        
        foreach ($fields as $field => $value) {
            switch ($field) {
                case "name":
                    $existingLocaleName = $entityManager->getRepository(Locale::class)->findOneBy(['name' => $fields['name']]);
                    if ($existingLocaleName !== null) {
                        return $this->json(['errors' => 'The name must be unique'], Response::HTTP_BAD_REQUEST);
                    }
                    $locale->setName($value);
                    break;
                case "ISO":
                    $existingISO = $entityManager->getRepository(Locale::class)->findOneBy(['ISO' => $fields['ISO']]);
                    if ($existingISO !== null) {
                        return $this->json(['errors' => 'The ISO must be unique'], Response::HTTP_BAD_REQUEST);
                    }
                    $locale->setISO($value);
                    break;
            }
        }

        $errors = $validator->validate($locale);

        $errors = $validator->validate($locale);
        if (count($errors) > 0) {
            return $this->json(['errors' => (string) $errors], Response::HTTP_BAD_REQUEST);
        }

        $entityManager->flush();

        return $this->json($locale);
    }

    #[Route("/locales/{id}", name: "delete_locale", methods: ["DELETE"])]
    public function deleteLocale(Locale $locale, LocaleRepository $localeRepository)
    {
        $localeRepository->remove($locale, true);

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }
}
