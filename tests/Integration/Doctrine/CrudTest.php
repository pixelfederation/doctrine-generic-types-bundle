<?php

declare(strict_types=1);

namespace PixelFederation\DoctrineGenericTypesBundle\Tests\Integration\Doctrine;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PixelFederation\DoctrineGenericTypesBundle\DependencyInjection\Configuration;
use PixelFederation\DoctrineGenericTypesBundle\DependencyInjection\PixelFederationDoctrineGenericTypesExtension;
use PixelFederation\DoctrineGenericTypesBundle\Doctrine\Connection\ConnectionFactory;
use PixelFederation\DoctrineGenericTypesBundle\Doctrine\TypeRegistry\DefaultTypeRegistryProvider;
use PixelFederation\DoctrineGenericTypesBundle\Doctrine\TypeRegistry\GenericTypesRegistrator;
use PixelFederation\DoctrineGenericTypesBundle\Doctrine\TypeRegistry\TypeRegistryProviderInterface;
use PixelFederation\DoctrineGenericTypesBundle\Tests\TestApplication\CustomValue\Currency;
use PixelFederation\DoctrineGenericTypesBundle\Tests\TestApplication\CustomValue\Price;
use PixelFederation\DoctrineGenericTypesBundle\Tests\TestApplication\Entity\Foo;
use PixelFederation\DoctrineGenericTypesBundle\Tests\TestApplication\OtherValue\Age;
use PixelFederation\DoctrineGenericTypesBundle\Tests\TestApplication\Value\Amount;
use PixelFederation\DoctrineGenericTypesBundle\Tests\TestApplication\Value\Count;
use PixelFederation\DoctrineGenericTypesBundle\Tests\TestApplication\Value\FirstName;
use PixelFederation\DoctrineGenericTypesBundle\Tests\TestApplication\Value\IsActive;
use PixelFederation\DoctrineGenericTypesBundle\Tests\TestApplication\Value\IsExpired;
use PixelFederation\DoctrineGenericTypesBundle\Tests\TestApplication\Value\LastName;
use PixelFederation\DoctrineGenericTypesBundle\Tests\TestApplication\Value\SuccessRate;
use PixelFederation\DoctrineGenericTypesBundle\Tests\TestApplication\Value\UserId;
use PixelFederation\DoctrineGenericTypesBundle\Tests\TestApplication\ValueWithoutGenericType\HeightInCm;
use Ramsey\Uuid\Uuid;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

#[CoversClass(ConnectionFactory::class)]
#[CoversClass(GenericTypesRegistrator::class)]
#[CoversClass(DefaultTypeRegistryProvider::class)]
#[CoversClass(TypeRegistryProviderInterface::class)]
#[CoversClass(Configuration::class)]
#[CoversClass(PixelFederationDoctrineGenericTypesExtension::class)]
final class CrudTest extends KernelTestCase
{
    public function testCrud(): void
    {
        self::bootKernel();
        $this->syncDb();

        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        self::assertInstanceOf(EntityManagerInterface::class, $entityManager);

        $all = $entityManager->getRepository(Foo::class)->findAll();
        self::assertCount(0, $all);

        $userId = new UserId(Uuid::uuid7());
        $firstName = new FirstName('John');
        $lastName = new LastName('Rambo');
        $isActive = new IsActive(true);
        $isExpired = new IsExpired(false);
        $amount = new Amount(100);
        $count = new Count(5);
        $successRate = new SuccessRate(99.99);
        $age = new Age(30);
        $heightInCm = new HeightInCm(180);
        $price = new Price(99.99, Currency::EUR);

        $newFoo = new Foo(
            userId: $userId,
            firstName: $firstName,
            lastName: $lastName,
            isActive: $isActive,
            isExpired: $isExpired,
            amount: $amount,
            count: $count,
            successRate: $successRate,
            age: $age,
            heightInCm: $heightInCm,
            price: $price,
        );
        $entityManager->persist($newFoo);
        $entityManager->flush();

        $all = $entityManager->getRepository(Foo::class)->findAll();
        self::assertCount(1, $all);

        $fooFromDb = $all[0];
        self::assertEquals($userId, $fooFromDb->userId);
        self::assertEquals($firstName, $fooFromDb->firstName);
        self::assertEquals($lastName, $fooFromDb->lastName);
        self::assertEquals($isActive, $fooFromDb->isActive);
        self::assertEquals($isExpired, $fooFromDb->isExpired);
        self::assertEquals($amount, $fooFromDb->amount);
        self::assertEquals($count, $fooFromDb->count);
        self::assertEquals($successRate, $fooFromDb->successRate);
        self::assertEquals($age, $fooFromDb->age);
        self::assertEquals($heightInCm, $fooFromDb->heightInCm);
        self::assertEquals($price, $fooFromDb->price);

        $updatedCount = new Count(10);
        $updatedPrice = new Price(10.5, Currency::USD);
        $fooFromDb->count = $updatedCount;
        $fooFromDb->price = $updatedPrice;
        $entityManager->flush();

        $updatedFoo = $entityManager->getRepository(Foo::class)->find($userId);
        self::assertEquals($updatedCount, $updatedFoo->count);
        self::assertEquals($updatedPrice, $updatedFoo->price);

        $entityManager->remove($updatedFoo);
        $entityManager->flush();

        $all = $entityManager->getRepository(Foo::class)->findAll();
        self::assertCount(0, $all);
    }

    private function syncDb(): void
    {
        $application = new Application(self::$kernel);
        $command = $application->find('doctrine:schema:update');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            '--force' => true,
        ]);
        $commandTester->assertCommandIsSuccessful();
    }
}
