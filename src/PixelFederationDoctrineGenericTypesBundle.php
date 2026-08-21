<?php

declare(strict_types=1);

namespace PixelFederation\DoctrineGenericTypesBundle;

use LogicException;
use Override;
use PixelFederation\DoctrineGenericTypesBundle\Doctrine\TypeRegistry\GenericTypesRegistrator;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * @psalm-suppress DeprecatedInterface
 */
final class PixelFederationDoctrineGenericTypesBundle extends Bundle
{
    #[Override]
    public function boot(): void
    {
        parent::boot();

        if ($this->container === null) {
            throw new LogicException('The bundle cannot be booted without a container.');
        }

        $genericTypesRegistrator = $this->container
            ->get('pixel_federation.doctrine_generic_types.generic_types_registrator');
        if (!$genericTypesRegistrator instanceof GenericTypesRegistrator) {
            throw new LogicException(sprintf(
                'Service "%s" must be an instance of %s.',
                'pixel_federation.doctrine_generic_types.generic_types_registrator',
                GenericTypesRegistrator::class,
            ));
        }

        $genericTypesRegistrator->register();
    }
}
