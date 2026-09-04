<?php

declare(strict_types=1);

namespace PixelFederation\DoctrineGenericTypesBundle\Tests\Unit\DependencyInjection;

use Doctrine\DBAL\Types\Type;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PixelFederation\DoctrineGenericTypesBundle\Bridge\RamseyUuid\Doctrine\Type\UuidValueType;
use PixelFederation\DoctrineGenericTypesBundle\Bridge\RamseyUuid\Value\UuidValue;
use PixelFederation\DoctrineGenericTypesBundle\DependencyInjection\Configuration;
use PixelFederation\DoctrineGenericTypesBundle\DependencyInjection\PixelFederationDoctrineGenericTypesExtension;
use PixelFederation\DoctrineGenericTypesBundle\Doctrine\Type\BooleanValueType;
use PixelFederation\DoctrineGenericTypesBundle\Doctrine\Type\FloatValueType;
use PixelFederation\DoctrineGenericTypesBundle\Doctrine\Type\GenericType;
use PixelFederation\DoctrineGenericTypesBundle\Doctrine\Type\IntegerValueType;
use PixelFederation\DoctrineGenericTypesBundle\Doctrine\Type\StringValueType;
use PixelFederation\DoctrineGenericTypesBundle\Doctrine\TypeRegistry\GenericTypeFactoryProvider;
use PixelFederation\DoctrineGenericTypesBundle\Tests\TestApplication\CustomValue\MoneyValue;
use PixelFederation\DoctrineGenericTypesBundle\Tests\TestApplication\CustomValue\Price;
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
use PixelFederation\DoctrineGenericTypesBundle\Value\BaseValue;
use PixelFederation\DoctrineGenericTypesBundle\Value\BooleanValue;
use PixelFederation\DoctrineGenericTypesBundle\Value\FloatValue;
use PixelFederation\DoctrineGenericTypesBundle\Value\IntegerValue;
use PixelFederation\DoctrineGenericTypesBundle\Value\StringValue;
use PixelFederation\DoctrineGenericTypesBundle\Value\Value;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\DependencyInjection\Reference;

#[CoversClass(PixelFederationDoctrineGenericTypesExtension::class)]
#[CoversClass(Configuration::class)]
final class PixelFederationDoctrineGenericTypesExtensionTest extends AbstractExtensionTestCase
{
    /**
     * @return array<string, array{
     *     genericTypes: array<string, string>,
     *     directories: array<string>,
     *     mapping: array<class-string<Value>, class-string<Type&GenericType>>
     * }>
     */
    public static function mappingDataProvider(): array
    {
        $genericTypes = [
            BooleanValue::class => BooleanValueType::class,
            FloatValue::class => FloatValueType::class,
            IntegerValue::class => IntegerValueType::class,
            UuidValue::class => UuidValueType::class,
            StringValue::class => StringValueType::class,
            MoneyValue::class => MoneyValueType::class,
        ];

        return [
            'empty' => [
                'genericTypes' => [],
                'directories' => [],
                'mapping' => [],
            ],
            'only_generic_types' => [
                'genericTypes' => $genericTypes,
                'directories' => [],
                'mapping' => [],
            ],
            'only_directories' => [
                'genericTypes' => [],
                'directories' => [
                    './tests/TestApplication',
                ],
                'mapping' => [],
            ],
            'invalid_directories' => [
                'genericTypes' => $genericTypes,
                'directories' => [
                    './tests/TestApplication/src/Exception',
                ],
                'mapping' => [],
            ],
            'ignored_directories' => [
                'genericTypes' => $genericTypes,
                'directories' => [
                    './tests/TestApplication/src/Value',
                ],
                'mapping' => [
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
            'multiple_directories' => [
                'genericTypes' => $genericTypes,
                'directories' => [
                    './tests/TestApplication/src/CustomValue',
                    './tests/TestApplication/src/OtherValue',
                    './tests/TestApplication/src/Value',
                ],
                'mapping' => [
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
            'last_matching_generic_type_wins' => [
                'genericTypes' => [
                    BaseValue::class => BooleanValueType::class,
                    IntegerValue::class => IntegerValueType::class,
                ],
                'directories' => [
                    './tests/TestApplication/src/OtherValue',
                ],
                'mapping' => [
                    Age::class => IntegerValueType::class,
                ],
            ],
            'mapping_priority_follows_configuration_order' => [
                'genericTypes' => [
                    IntegerValue::class => IntegerValueType::class,
                    BaseValue::class => BooleanValueType::class,
                ],
                'directories' => [
                    './tests/TestApplication/src/OtherValue',
                ],
                'mapping' => [
                    Age::class => BooleanValueType::class,
                ],
            ],
        ];
    }

    public function testEmptyConfig(): void
    {
        $this->load();

        $this->assertContainerBuilderHasParameter(
            'pixel_federation.doctrine_generic_types.generic_types_mapping',
            [],
        );
    }

    public function testStaticGenericTypeFactoryHasDefaultPriority(): void
    {
        $this->load();

        $factory = $this->container->getDefinition(
            'pixel_federation.doctrine_generic_types.static_generic_type_factory',
        );

        self::assertSame(
            [['priority' => GenericTypeFactoryProvider::DEFAULT_PRIORITY]],
            $factory->getTag(GenericTypeFactoryProvider::TAG),
        );
    }

    public function testSerializerBridgeConfiguration(): void
    {
        $serializerBridges = [
            'symfony' => ['service' => 'app.serializer.symfony'],
            'jms' => ['service' => 'app.serializer.jms'],
        ];

        $this->load(['serializer_bridges' => $serializerBridges]);

        $symfonyFactory = $this->container->getDefinition(
            'pixel_federation.doctrine_generic_types.symfony_serializer_generic_type_factory',
        );
        $jmsFactory = $this->container->getDefinition(
            'pixel_federation.doctrine_generic_types.jms_serializer_generic_type_factory',
        );

        self::assertEquals(new Reference('app.serializer.symfony'), $symfonyFactory->getArgument('$serializer'));
        self::assertEquals(new Reference('app.serializer.jms'), $jmsFactory->getArgument('$serializer'));
        $factoryTag = [['priority' => GenericTypeFactoryProvider::DEFAULT_PRIORITY]];
        self::assertSame($factoryTag, $symfonyFactory->getTag(GenericTypeFactoryProvider::TAG));
        self::assertSame($factoryTag, $jmsFactory->getTag(GenericTypeFactoryProvider::TAG));
    }

    public function testSerializerBridgeRequiresServiceId(): void
    {
        $this->expectException(InvalidConfigurationException::class);

        $this->load([
            'serializer_bridges' => [
                'symfony' => [],
            ],
        ]);
    }

    public function testGenericTypeMappingRejectsClassThatIsNotValue(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage('must implement ' . Value::class);

        $this->load([
            'generic_types' => [
                self::class => StringValueType::class,
            ],
        ]);
    }

    /**
     * @param array<string, string> $genericTypes
     * @param array<string> $directories
     * @param array<class-string<Value>, class-string<Type&GenericType>> $mapping
     */
    #[DataProvider('mappingDataProvider')]
    public function testMapping(array $genericTypes, array $directories, array $mapping): void
    {
        $this->load([
            'generic_types' => $genericTypes,
            'directories' => $directories,
        ]);

        $this->assertContainerBuilderHasParameter('pixel_federation.doctrine_generic_types.generic_types_mapping');
        $param = $this->container->getParameter('pixel_federation.doctrine_generic_types.generic_types_mapping');
        ksort($param);
        ksort($mapping);
        self::assertSame($mapping, $param);
    }

    /**
     * @inheritDoc
     */
    #[Override]
    protected function getContainerExtensions(): array
    {
        return [
            new PixelFederationDoctrineGenericTypesExtension(),
        ];
    }
}
