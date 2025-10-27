<?php

declare(strict_types=1);

namespace PixelFederation\DoctrineGenericTypesBundle\Tests\Unit\Doctrine\TypeRegistry;

use Doctrine\DBAL\Types\Type;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use PixelFederation\DoctrineGenericTypesBundle\Bridge\RamseyUuid\Doctrine\Type\UuidValueType;
use PixelFederation\DoctrineGenericTypesBundle\Doctrine\Type\BaseGenericType;
use PixelFederation\DoctrineGenericTypesBundle\Doctrine\Type\BooleanValueType;
use PixelFederation\DoctrineGenericTypesBundle\Doctrine\Type\FloatValueType;
use PixelFederation\DoctrineGenericTypesBundle\Doctrine\Type\GenericType;
use PixelFederation\DoctrineGenericTypesBundle\Doctrine\Type\IntegerValueType;
use PixelFederation\DoctrineGenericTypesBundle\Doctrine\Type\StringValueType;
use PixelFederation\DoctrineGenericTypesBundle\Doctrine\TypeRegistry\GenericTypesRegistrator;
use PixelFederation\DoctrineGenericTypesBundle\Tests\TestApplication\CustomValue\Price;
use PixelFederation\DoctrineGenericTypesBundle\Tests\TestApplication\Doctrine\Type\AgeType;
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
use PixelFederation\DoctrineGenericTypesBundle\Value\Value;
use ReflectionClass;

#[CoversClass(GenericTypesRegistrator::class)]
final class GenericTypesRegistratorTest extends TestCase
{
    /**
     * @return array<string, array{
     *     genericTypesMapping: array<class-string<Value>, class-string<GenericType&Type>>,
     * }>
     */
    public static function genericTypesMappingDataProvider(): array
    {
        return [
            'empty' => [
                'genericTypesMapping' => [],
            ],
            'all' => [
                'genericTypesMapping' => [
                    Price::class => MoneyValueType::class,
                    Age::class => IntegerValueType::class,
                    IsActive::class => BooleanValueType::class,
                    IsExpired::class => BooleanValueType::class,
                    Count::class => IntegerValueType::class,
                    FirstName::class => StringValueType::class,
                    SuccessRate::class => FloatValueType::class,
                    LastName::class => StringValueType::class,
                    Amount::class => IntegerValueType::class,
                    UserId::class => UuidValueType::class,
                ],
            ],
        ];
    }

    /**
     * @param array<class-string<Value>, class-string<GenericType&Type>> $genericTypesMapping
     */
    #[DataProvider('genericTypesMappingDataProvider')]
    public function testRegister(array $genericTypesMapping): void
    {
        $typeRegistryProvider = new TypeRegistryProvider();
        $typeRegistry = $typeRegistryProvider->provide();
        $genericTypesRegistrator = new GenericTypesRegistrator(
            $typeRegistryProvider,
            $genericTypesMapping,
        );
        $initTypesCount = count($typeRegistry->getMap());

        $genericTypesRegistrator->register();

        foreach ($genericTypesMapping as $value => $type) {
            self::assertTrue($typeRegistry->has($value));

            $registeredType = $typeRegistry->get($value);
            self::assertInstanceOf($type, $registeredType);
            self::assertSame($value, $registeredType->getName());
            if (!$registeredType instanceof BaseGenericType) {
                continue;
            }
            $registeredTypeReflection = new ReflectionClass($registeredType);
            self::assertSame(
                $value,
                $registeredTypeReflection->getProperty('class')->getValue($registeredType),
            );
        }

        self::assertCount($initTypesCount + count($genericTypesMapping), $typeRegistry->getMap());
    }

    public function testDuplicity(): void
    {
        $typeRegistryProvider = new TypeRegistryProvider();
        $typeRegistry = $typeRegistryProvider->provide();

        $initTypesCount = count($typeRegistry->getMap());
        $initAgeType = new AgeType();
        $typeRegistry->register(Age::class, $initAgeType);

        $genericTypesRegistrator = new GenericTypesRegistrator(
            $typeRegistryProvider,
            [
                Age::class => IntegerValueType::class,
            ],
        );
        $genericTypesRegistrator->register();

        self::assertTrue($typeRegistry->has(Age::class));
        $registeredType = $typeRegistry->get(Age::class);
        self::assertSame($initAgeType, $registeredType);

        self::assertCount($initTypesCount + 1, $typeRegistry->getMap());
    }
}
