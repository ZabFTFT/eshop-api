<?php

namespace App\Command;

use App\Entity\Country;
use App\Entity\Locale;
use App\Entity\Product;
use App\Entity\VatRate;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:populate-db',
    description: 'Add a short description for your command',
)]
class PopulateDatabaseCommand extends Command
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Populates the database with initial data');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $entityManager = $this->entityManager;


        $locale1 = new Locale();
        $locale1->setName("English");
        $locale1->setISO("en");

        $locale2 = new Locale();
        $locale2->setName("French");
        $locale2->setISO("fr");

        $country1 = new Country();
        $country1->setName('US');
        $country1->setLocale($locale1);

        $country2 = new Country();
        $country2->setName('France');
        $country2->setLocale($locale2);

        $product1 = new Product();
        $product1->setName("Bread");
        $product1->setDescription("The best bread in the world!");
        $product1->setPrice(12.5);

        $product2 = new Product();
        $product2->setName("Wine");
        $product2->setDescription("The best wine in the world!");
        $product2->setPrice(51.25);

        $vatRate1Country1Product1 = new VatRate();
        $vatRate1Country1Product1->setRate(5);
        $vatRate1Country1Product1->setCountry($country1);
        $vatRate1Country1Product1->setProduct($product1);

        $vatRate2Country1Product2 = new VatRate();
        $vatRate2Country1Product2->setRate(15);
        $vatRate2Country1Product2->setCountry($country1);
        $vatRate2Country1Product2->setProduct($product2);

        $vatRate3Country2Product1 = new VatRate();
        $vatRate3Country2Product1->setRate(8);
        $vatRate3Country2Product1->setCountry($country2);
        $vatRate3Country2Product1->setProduct($product1);

        $vatRate4Country2Product2 = new VatRate();
        $vatRate4Country2Product2->setRate(2);
        $vatRate4Country2Product2->setCountry($country2);
        $vatRate4Country2Product2->setProduct($product2);

        $entityManager->persist($locale1);
        $entityManager->persist($locale2);
        $entityManager->persist($country1);
        $entityManager->persist($country2);
        $entityManager->persist($product1);
        $entityManager->persist($product2);
        $entityManager->persist($vatRate1Country1Product1);
        $entityManager->persist($vatRate2Country1Product2);
        $entityManager->persist($vatRate3Country2Product1);
        $entityManager->persist($vatRate4Country2Product2);

        $entityManager->flush();

        $output->writeln('Database populated with initial data.');

        return Command::SUCCESS;
    }
}
