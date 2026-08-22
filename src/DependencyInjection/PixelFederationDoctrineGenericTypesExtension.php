<?php

declare(strict_types=1);

namespace PixelFederation\DoctrineGenericTypesBundle\DependencyInjection;

use Composer\ClassMapGenerator\ClassMapGenerator;
use Override;
use PixelFederation\DoctrineGenericTypesBundle\Bridge\JmsSerializer\Doctrine\TypeRegistry\JmsSerializerGenericTypeFactory;
use PixelFederation\DoctrineGenericTypesBundle\Bridge\SymfonySerializer\Doctrine\TypeRegistry\SymfonySerializerGenericTypeFactory;
use PixelFederation\DoctrineGenericTypesBundle\Doctrine\Type\GenericType;
use PixelFederation\DoctrineGenericTypesBundle\Doctrine\TypeRegistry\GenericTypeFactoryProvider;
use PixelFederation\DoctrineGenericTypesBundle\Value\Value;
use ReflectionClass;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;

/**
 * @psalm-type ValueClass = class-string<Value<mixed>>
 * @psalm-type GenericTypeClass = class-string<GenericType>
 * @psalm-type GenericTypesMapping = array<ValueClass, GenericTypeClass>
 * @psalm-type Directories = array<string>
 * @psalm-type ValueClasses = array<ValueClass>
 */
final class PixelFederationDoctrineGenericTypesExtension extends ConfigurableExtension
{
    /**
     * @param array<array-key, mixed> $mergedConfig
     */
    #[Override]
    protected function loadInternal(array $mergedConfig, ContainerBuilder $container): void
    {
        $loader = new PhpFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.php');

        $genericTypes = $this->getGenericTypesMapping($mergedConfig['generic_types']);
        $directories = $this->getDirectories($mergedConfig['directories']);
        $serializerBridges = $this->getSerializerBridges($mergedConfig['serializer_bridges']);

        $mapping = $this->createDoctrineTypesMapping($genericTypes, $directories);
        $container->setParameter('pixel_federation.doctrine_generic_types.generic_types_mapping', $mapping);
        $this->registerSerializerBridges($container, $serializerBridges);
    }

    /**
     * @param GenericTypesMapping $genericTypes
     * @param Directories $directories
     * @return GenericTypesMapping
     */
    private function createDoctrineTypesMapping(array $genericTypes, array $directories): array
    {
        $allValues = $this->findAllValues($directories);
        $mapping = [];
        foreach ($allValues as $value) {
            $genericType = $this->findGenericType($value, $genericTypes);
            if ($genericType === null) {
                continue;
            }

            $mapping[$value] = $genericType;
        }

        return $mapping;
    }

    /**
     * @param Directories $directories
     * @return ValueClasses
     */
    private function findAllValues(array $directories): array
    {
        $map = [];
        foreach ($directories as $dir) {
            $map += ClassMapGenerator::createMap($dir);
        }

        $result = [];
        foreach (array_keys($map) as $className) {
            $valueClass = $this->getInstantiableValueClass($className);
            if ($valueClass === null) {
                continue;
            }
            $result[] = $valueClass;
        }

        return $result;
    }

    /**
     * @param ValueClass $value
     * @param GenericTypesMapping $genericTypes
     * @return GenericTypeClass|null
     */
    private function findGenericType(string $value, array $genericTypes): ?string
    {
        $genericType = null;
        foreach ($genericTypes as $dbValue => $dbType) {
            if (!is_a($value, $dbValue, true)) {
                continue;
            }

            $genericType = $dbType;
        }

        return $genericType;
    }

    /**
     * @return ValueClass|null
     */
    private function getInstantiableValueClass(string $className): ?string
    {
        if (!is_subclass_of($className, Value::class)) {
            return null;
        }

        return new ReflectionClass($className)->isInstantiable() ? $className : null;
    }

    /**
     * @return GenericTypesMapping
     */
    private function getGenericTypesMapping(mixed $configuredGenericTypes): array
    {
        $this->assertCondition(
            is_array($configuredGenericTypes),
            'The "generic_types" configuration must be an array.',
        );

        $genericTypes = [];
        foreach ($configuredGenericTypes as $valueClass => $genericTypeClass) {
            $this->assertCondition(
                is_string($valueClass),
                'Every "generic_types" configuration key must be a class name.',
            );
            $this->assertCondition(
                is_subclass_of($valueClass, Value::class),
                sprintf('Configured value class "%s" must implement %s.', $valueClass, Value::class),
            );
            $this->assertCondition(
                is_string($genericTypeClass),
                sprintf('Generic type configured for "%s" must be a class name.', $valueClass),
            );
            $this->assertCondition(
                is_subclass_of($genericTypeClass, GenericType::class),
                sprintf('Configured generic type "%s" must implement %s.', $genericTypeClass, GenericType::class),
            );

            $genericTypes[$valueClass] = $genericTypeClass;
        }

        return $genericTypes;
    }

    /**
     * @return Directories
     */
    private function getDirectories(mixed $configuredDirectories): array
    {
        $this->assertCondition(
            is_array($configuredDirectories),
            'The "directories" configuration must be an array.',
        );

        $directories = [];
        foreach ($configuredDirectories as $directory) {
            $this->assertCondition(
                is_string($directory),
                'Every configured directory must be a string.',
            );
            $directories[] = $directory;
        }

        return $directories;
    }

    /**
     * @return array<string, array{service: string}>
     */
    private function getSerializerBridges(mixed $configuredBridges): array
    {
        $this->assertCondition(
            is_array($configuredBridges),
            'The "serializer_bridges" configuration must be an array.',
        );

        $bridges = [];
        foreach (['symfony', 'jms'] as $bridge) {
            if (!isset($configuredBridges[$bridge])) {
                continue;
            }

            $configuration = $configuredBridges[$bridge];
            $this->assertCondition(is_array($configuration), sprintf('The "%s" bridge must be an array.', $bridge));
            $service = $configuration['service'] ?? null;
            $this->assertCondition(is_string($service), sprintf(
                'The "%s" serializer bridge service must be a string.',
                $bridge,
            ));
            $bridges[$bridge] = ['service' => $service];
        }

        return $bridges;
    }

    /**
     * @param array<string, array{service: string}> $bridges
     */
    private function registerSerializerBridges(ContainerBuilder $container, array $bridges): void
    {
        $this->registerSymfonySerializerBridge($container, $bridges['symfony']['service'] ?? null);
        $this->registerJmsSerializerBridge($container, $bridges['jms']['service'] ?? null);
    }

    private function registerSymfonySerializerBridge(ContainerBuilder $container, ?string $service): void
    {
        if ($service === null) {
            return;
        }

        $container
            ->register(
                'pixel_federation.doctrine_generic_types.symfony_serializer_generic_type_factory',
                SymfonySerializerGenericTypeFactory::class,
            )
            ->setArgument('$serializer', new Reference($service))
            ->addTag(
                GenericTypeFactoryProvider::TAG,
                ['priority' => GenericTypeFactoryProvider::DEFAULT_PRIORITY],
            );
    }

    private function registerJmsSerializerBridge(ContainerBuilder $container, ?string $service): void
    {
        if ($service === null) {
            return;
        }

        $container
            ->register(
                'pixel_federation.doctrine_generic_types.jms_serializer_generic_type_factory',
                JmsSerializerGenericTypeFactory::class,
            )
            ->setArgument('$serializer', new Reference($service))
            ->addTag(
                GenericTypeFactoryProvider::TAG,
                ['priority' => GenericTypeFactoryProvider::DEFAULT_PRIORITY],
            );
    }

    /**
     * @phpstan-assert true $condition
     * @psalm-assert true $condition
     */
    private function assertCondition(bool $condition, string $message): void
    {
        if (!$condition) {
            throw new InvalidConfigurationException($message);
        }
    }
}
