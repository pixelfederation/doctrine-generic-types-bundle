<?php

declare(strict_types=1);

namespace PixelFederation\DoctrineGenericTypesBundle\Doctrine\Connection;

use Doctrine\Bundle\DoctrineBundle\ConnectionFactory as DoctrineConnectionFactory;
use Doctrine\Common\EventManager;
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use PixelFederation\DoctrineGenericTypesBundle\Doctrine\TypeRegistry\GenericTypesRegistrator;

/**
 * @phpstan-import-type Params from DriverManager
 */
final class ConnectionFactory
{
    public function __construct(
        private readonly DoctrineConnectionFactory $connectionFactory,
        private readonly GenericTypesRegistrator $genericTypesRegistrator,
    ) {
    }

    /**
     * @param array<string, string> $mappingTypes
     * @phpstan-param Params $params
     */
    public function createConnection(
        array $params,
        ?Configuration $config = null,
        ?EventManager $eventManager = null,
        array $mappingTypes = [],
    ): Connection {
        $this->genericTypesRegistrator->register();

        return $this->connectionFactory->createConnection($params, $config, $eventManager, $mappingTypes);
    }
}
