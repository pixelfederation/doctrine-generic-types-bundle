<?php

declare(strict_types=1);

namespace PixelFederation\DoctrineGenericTypesBundle\Tests\TestApplication;

use DAMA\DoctrineTestBundle\DAMADoctrineTestBundle;
use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Override;
use PixelFederation\DoctrineGenericTypesBundle\PixelFederationDoctrineGenericTypesBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\HttpKernel\Kernel as SymfonyKernel;

final class Kernel extends SymfonyKernel
{
    use MicroKernelTrait;

    /**
     * @inheritDoc
     */
    #[Override]
    public function registerBundles(): iterable
    {
        return [
            new FrameworkBundle(),
            new DoctrineBundle(),
            new PixelFederationDoctrineGenericTypesBundle(),
            new DAMADoctrineTestBundle(),
        ];
    }

    #[Override]
    public function getProjectDir(): string
    {
        return dirname(__DIR__);
    }
}
