<?php

declare(strict_types=1);

namespace PixelFederation\DoctrineGenericTypesBundle\Command;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Type;
use Doctrine\Persistence\ManagerRegistry;
use PixelFederation\DoctrineGenericTypesBundle\Doctrine\Type\GenericType;
use PixelFederation\DoctrineGenericTypesBundle\Doctrine\TypeRegistry\TypeRegistryProviderInterface;
use PixelFederation\DoctrineGenericTypesBundle\Value\Value;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Attribute\Option;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableCell;
use Symfony\Component\Console\Helper\TableCellStyle;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'pxfd:doctrine_generic_types:list',
    description: 'List all registered doctrine types',
)]
final class ListCommand extends Command
{
    public function __construct(
        private readonly ManagerRegistry $registry,
        private readonly TypeRegistryProviderInterface $typeRegistryProvider,
    ) {
        parent::__construct();
    }

    /**
     * @SuppressWarnings("PHPMD.BooleanArgumentFlag"))
     */
    public function __invoke(
        SymfonyStyle $symfonyStyle,
        #[Option(
            description: 'Show all types',
            name: 'all',
        )]
        bool $all = false,
    ): int {
        $this->ensureGenericTypesRegistration();

        $table = $symfonyStyle->createTable();
        $table->setHeaders(['Value', 'Is Value', 'Type', 'Is Generic Type']);
        $typesMap = $this->getTypesMap();
        foreach ($typesMap as $name => $type) {
            $this->addTableRow($name, $type, $all, $table);
        }
        $table->render();

        return Command::SUCCESS;
    }

    private function addTableRow(string $name, string $type, bool $showAll, Table $table): void
    {
        $isGenericType = is_subclass_of($type, GenericType::class);
        if (!$showAll && !$isGenericType) {
            return;
        }
        $isValue = is_subclass_of($name, Value::class);
        $valueNotGenericType = $isValue && !$isGenericType;
        $table->addRow($this->createRowData($name, $isValue, $type, $valueNotGenericType, $isGenericType));
    }

    /**
     * @return array{0: string, 1: string, 2: TableCell, 3: TableCell}
     */
    private function createRowData(
        string $name,
        bool $isValue,
        string $type,
        bool $valueNotGenericType,
        bool $isGenericType,
    ): array {
        $styleOption = $valueNotGenericType ? ['style' => new TableCellStyle(['fg' => 'red'])] : [];

        return [
            $name,
            $isValue ? 'Yes' : 'No',
            new TableCell($type, $styleOption),
            new TableCell(
                $isGenericType ? 'Yes' : 'No',
                $styleOption,
            ),
        ];
    }

    /**
     * @see Type::getTypesMap()
     * @return array<string, string>
     */
    private function getTypesMap(): array
    {
        $typeRepository = $this->typeRegistryProvider->provide();

        /**
         * @psalm-suppress InternalMethod
         */
        return array_map(
            static function (Type $type): string {
                return $type::class;
            },
            $typeRepository->getMap(),
        );
    }

    private function ensureGenericTypesRegistration(): void
    {
        $connection = $this->registry->getConnection();
        assert($connection instanceof Connection);
        $platform = $connection->getDatabasePlatform();
        $query = $platform->getDummySelectSQL();
        $connection->executeQuery($query);
    }
}
