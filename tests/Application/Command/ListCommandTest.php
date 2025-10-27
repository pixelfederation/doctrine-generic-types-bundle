<?php

declare(strict_types=1);

namespace PixelFederation\DoctrineGenericTypesBundle\Tests\Application\Command;

use PHPUnit\Framework\Attributes\CoversClass;
use PixelFederation\DoctrineGenericTypesBundle\Bridge\RamseyUuid\Doctrine\Type\UuidValueType;
use PixelFederation\DoctrineGenericTypesBundle\Command\ListCommand;
use PixelFederation\DoctrineGenericTypesBundle\DependencyInjection\Configuration;
use PixelFederation\DoctrineGenericTypesBundle\DependencyInjection\PixelFederationDoctrineGenericTypesExtension;
use PixelFederation\DoctrineGenericTypesBundle\Doctrine\Connection\ConnectionFactory;
use PixelFederation\DoctrineGenericTypesBundle\Doctrine\Type\BooleanValueType;
use PixelFederation\DoctrineGenericTypesBundle\Doctrine\Type\FloatValueType;
use PixelFederation\DoctrineGenericTypesBundle\Doctrine\Type\IntegerValueType;
use PixelFederation\DoctrineGenericTypesBundle\Doctrine\Type\StringValueType;
use PixelFederation\DoctrineGenericTypesBundle\Doctrine\TypeRegistry\GenericTypesRegistrator;
use PixelFederation\DoctrineGenericTypesBundle\Tests\TestApplication\CustomValue\Price;
use PixelFederation\DoctrineGenericTypesBundle\Tests\TestApplication\Doctrine\Type\HeightInCmType;
use PixelFederation\DoctrineGenericTypesBundle\Tests\TestApplication\Doctrine\Type\MoneyValueType;
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
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

#[CoversClass(ListCommand::class)]
#[CoversClass(Configuration::class)]
#[CoversClass(PixelFederationDoctrineGenericTypesExtension::class)]
#[CoversClass(ConnectionFactory::class)]
#[CoversClass(GenericTypesRegistrator::class)]
final class ListCommandTest extends KernelTestCase
{
    private const string ROW_PATTERN = ' %s %s %s %s';

    public function testWithAll(): void
    {
        self::bootKernel();
        $commandTester = $this->executeCommand([
            '--all' => true,
        ]);
        $commandTester->assertCommandIsSuccessful();

        // the output of the command in the console
        $output = $commandTester->getDisplay();
        $outputWithSimpleSpace = preg_replace('/ {2,}/', ' ', $output);

        $this->assertGenerics($outputWithSimpleSpace);

        $valuesWithoutGenericTypes = [
            HeightInCm::class => HeightInCmType::class,
        ];
        foreach ($valuesWithoutGenericTypes as $value => $type) {
            self::assertStringContainsStringIgnoringCase(
                sprintf(self::ROW_PATTERN, $value, 'Yes', $type, 'No'),
                $outputWithSimpleSpace,
            );
        }
    }

    public function testWithoutAll(): void
    {
        self::bootKernel();
        $commandTester = $this->executeCommand([]);
        $commandTester->assertCommandIsSuccessful();

        // the output of the command in the console
        $output = $commandTester->getDisplay();
        $outputWithSimpleSpace = preg_replace('/ {2,}/', ' ', $output);

        $this->assertGenerics($outputWithSimpleSpace);
    }

    private function assertGenerics(string $output): void
    {
        $generics = [
            IsActive::class => BooleanValueType::class,
            IsExpired::class => BooleanValueType::class,
            Count::class => IntegerValueType::class,
            FirstName::class => StringValueType::class,
            SuccessRate::class => FloatValueType::class,
            LastName::class => StringValueType::class,
            Amount::class => IntegerValueType::class,
            UserId::class => UuidValueType::class,
            Age::class => IntegerValueType::class,
            Price::class => MoneyValueType::class,
        ];

        foreach ($generics as $value => $type) {
            self::assertStringContainsStringIgnoringCase(
                sprintf(self::ROW_PATTERN, $value, 'Yes', $type, 'Yes'),
                $output,
            );
        }
    }

    /**
     * @param array<string, mixed> $input
     */
    private function executeCommand(array $input): CommandTester
    {
        $application = new Application(self::$kernel);
        $command = $application->find('pxfd:doctrine_generic_types:list');
        $commandTester = new CommandTester($command);
        $commandTester->execute($input);

        return $commandTester;
    }
}
