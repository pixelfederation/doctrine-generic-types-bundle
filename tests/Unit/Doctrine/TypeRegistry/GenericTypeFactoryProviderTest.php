<?php

declare(strict_types=1);

namespace PixelFederation\DoctrineGenericTypesBundle\Tests\Unit\Doctrine\TypeRegistry;

use LogicException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use PixelFederation\DoctrineGenericTypesBundle\Doctrine\Type\StringValueType;
use PixelFederation\DoctrineGenericTypesBundle\Doctrine\TypeRegistry\GenericTypeFactoryProvider;
use PixelFederation\DoctrineGenericTypesBundle\Doctrine\TypeRegistry\StaticGenericTypeFactory;
use PixelFederation\DoctrineGenericTypesBundle\Tests\TestApplication\Value\FirstName;

#[CoversClass(GenericTypeFactoryProvider::class)]
#[CoversClass(StaticGenericTypeFactory::class)]
final class GenericTypeFactoryProviderTest extends TestCase
{
    public function testProvidesSupportingFactory(): void
    {
        $factory = new StaticGenericTypeFactory();
        $provider = new GenericTypeFactoryProvider([$factory]);

        self::assertTrue($factory->supports(StringValueType::class));
        self::assertSame($factory, $provider->provide(StringValueType::class));
        self::assertInstanceOf(StringValueType::class, $factory->create(StringValueType::class, FirstName::class));
    }

    public function testThrowsWhenNoFactorySupportsType(): void
    {
        $provider = new GenericTypeFactoryProvider([new StaticGenericTypeFactory()]);

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage(UnsupportedGenericType::class);

        $provider->provide(UnsupportedGenericType::class);
    }
}
