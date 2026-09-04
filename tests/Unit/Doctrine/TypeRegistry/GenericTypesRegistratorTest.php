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
use PixelFederation\DoctrineGenericTypesBundle\Doctrine\TypeRegistry\GenericTypeFactoryProvider;
use PixelFederation\DoctrineGenericTypesBundle\Doctrine\TypeRegistry\GenericTypesRegistrator;
use PixelFederation\DoctrineGenericTypesBundle\Doctrine\TypeRegistry\StaticGenericTypeFactory;
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
            self::createFactoryProvider(),
            $genericTypesMapping,
        );
        $genericTypesRegistrator->register();

        foreach ($genericTypesMapping as $value => $type) {
            self::assertTrue($typeRegistry->has($value));

            $registeredType = $typeRegistry->get($value);
            self::assertInstanceOf($type, $registeredType);
            self::assertSame($value, $typeRegistry->lookupName($registeredType));
            if (!$registeredType instanceof BaseGenericType) {
                continue;
            }
            $registeredTypeReflection = new ReflectionClass($registeredType);
            self::assertSame(
                $value,
                $registeredTypeReflection->getProperty('class')->getValue($registeredType),
            );
        }
    }

    public function testEmptyMapping(): void
    {
        $typeRegistryProvider = new TypeRegistryProvider();
        $typeRegistry = $typeRegistryProvider->provide();
        $genericTypesRegistrator = new GenericTypesRegistrator(
            $typeRegistryProvider,
            self::createFactoryProvider(),
        );

        self::assertFalse($typeRegistry->has(FirstName::class));
        $genericTypesRegistrator->register();
        self::assertFalse($typeRegistry->has(FirstName::class));
    }

    public function testDuplicity(): void
    {
        $typeRegistryProvider = new TypeRegistryProvider();
        $typeRegistry = $typeRegistryProvider->provide();

        $initAgeType = new AgeType();
        $typeRegistry->register(Age::class, $initAgeType);

        $genericTypesRegistrator = new GenericTypesRegistrator(
            $typeRegistryProvider,
            self::createFactoryProvider(),
            [
                Age::class => IntegerValueType::class,
            ],
        );
        $genericTypesRegistrator->register();

        self::assertTrue($typeRegistry->has(Age::class));
        $registeredType = $typeRegistry->get(Age::class);
        self::assertSame($initAgeType, $registeredType);
    }

    public function testRepeatedRegistrationIsIdempotent(): void
    {
        $typeRegistryProvider = new TypeRegistryProvider();
        $typeRegistry = $typeRegistryProvider->provide();
        $genericTypesRegistrator = new GenericTypesRegistrator(
            $typeRegistryProvider,
            self::createFactoryProvider(),
            [
                FirstName::class => StringValueType::class,
                IsActive::class => BooleanValueType::class,
            ],
        );

        $genericTypesRegistrator->register();
        $registeredFirstNameType = $typeRegistry->get(FirstName::class);
        $registeredIsActiveType = $typeRegistry->get(IsActive::class);
        $genericTypesRegistrator->register();

        self::assertSame($registeredFirstNameType, $typeRegistry->get(FirstName::class));
        self::assertSame($registeredIsActiveType, $typeRegistry->get(IsActive::class));
    }

    private static function createFactoryProvider(): GenericTypeFactoryProvider
    {
        return new GenericTypeFactoryProvider([new StaticGenericTypeFactory()]);
    }
}
