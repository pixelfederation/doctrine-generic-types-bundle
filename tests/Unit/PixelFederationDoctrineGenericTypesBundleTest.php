<?php

declare(strict_types=1);

namespace PixelFederation\DoctrineGenericTypesBundle\Tests\Unit;

use LogicException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use PixelFederation\DoctrineGenericTypesBundle\PixelFederationDoctrineGenericTypesBundle;
use stdClass;
use Symfony\Component\DependencyInjection\ContainerInterface;

#[CoversClass(PixelFederationDoctrineGenericTypesBundle::class)]
final class PixelFederationDoctrineGenericTypesBundleTest extends TestCase
{
    public function testBootWithoutContainerFails(): void
    {
        $bundle = new PixelFederationDoctrineGenericTypesBundle();
        $bundle->setContainer(null);

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('The bundle cannot be booted without a container.');

        $bundle->boot();
    }

    public function testBootWithInvalidRegistratorServiceFails(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $container
            ->expects($this->once())
            ->method('get')
            ->with('pixel_federation.doctrine_generic_types.generic_types_registrator')
            ->willReturn(new stdClass());

        $bundle = new PixelFederationDoctrineGenericTypesBundle();
        $bundle->setContainer($container);

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('must be an instance of');

        $bundle->boot();
    }
}
