<?php

declare(strict_types=1);

namespace PixelFederation\DoctrineGenericTypesBundle\DependencyInjection;

use Composer\ClassMapGenerator\ClassMapGenerator;
use Override;
use PixelFederation\DoctrineGenericTypesBundle\Doctrine\Type\GenericType;
use PixelFederation\DoctrineGenericTypesBundle\Value\Value;
use ReflectionClass;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;

final class PixelFederationDoctrineGenericTypesExtension extends ConfigurableExtension
{
    /**
     * @param array{
     *     generic_types: array<class-string<Value<mixed>>, class-string<GenericType>>,
     *     directories: array<string>,
     * } $mergedConfig
     * @psalm-suppress MoreSpecificImplementedParamType
     */
    #[Override]
    protected function loadInternal(array $mergedConfig, ContainerBuilder $container): void
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yaml');

        $mapping = $this->createDoctrineTypesMapping($mergedConfig['generic_types'], $mergedConfig['directories']);
        $container->setParameter('pixel_federation.doctrine_generic_types.generic_types_mapping', $mapping);
    }

    /**
     * @param array<class-string<Value<mixed>>, class-string<GenericType>> $genericTypes
     * @param array<string> $directories
     * @return array<class-string<Value<mixed>>, class-string<GenericType>>
     */
    // phpcs:ignore SlevomatCodingStandard.Complexity.Cognitive.ComplexityTooHigh
    private function createDoctrineTypesMapping(array $genericTypes, array $directories): array
    {
        $allValues = $this->findAllValues($directories);
        $mapping = [];
        foreach ($allValues as $value) {
            foreach ($genericTypes as $dbValue => $dbType) {
                if (!is_a($value, $dbValue, true)) {
                    continue;
                }

                $mapping[$value] = $dbType;
            }
        }

        return $mapping;
    }

    /**
     * @param array<string> $directories
     * @return array<class-string<Value<mixed>>>
     */
    private function findAllValues(array $directories): array
    {
        $map = [];
        foreach ($directories as $dir) {
            $map += ClassMapGenerator::createMap($dir);
        }

        /**
         * @var array<class-string<Value<mixed>>> $result
         */
        $result = [];
        foreach (array_keys($map) as $className) {
            if (!$this->isClassInstantiableValue($className)) {
                continue;
            }
            // phpcs:ignore SlevomatCodingStandard.Commenting.InlineDocCommentDeclaration.NoAssignment
            /** @var class-string<Value<mixed>> $className */
            $result[] = $className;
        }

        return $result;
    }

    private function isClassInstantiableValue(string $className): bool
    {
        if (!is_subclass_of($className, Value::class)) {
            return false;
        }

        return (new ReflectionClass($className))->isInstantiable();
    }
}
