<?php

declare(strict_types=1);

namespace PixelFederation\DoctrineGenericTypesBundle\Command;

use Doctrine\DBAL\Types\Type;
use Doctrine\Persistence\ManagerRegistry;
use PixelFederation\DoctrineGenericTypesBundle\Doctrine\Type\GenericType;
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
    ) {
        parent::__construct();
    }

    public function __invoke(
        SymfonyStyle $symfonyStyle,
        #[Option(
            description: 'Show all types',
            name: 'all',
        )]
        bool $all = false,
    ): int {
        $this->initializeDoctrineTypes();

        $table = $symfonyStyle->createTable();
        $table->setHeaders(['Value', 'Is Value', 'Type', 'Is Generic Type']);
        $typesMap = Type::getTypesMap();
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

    private function initializeDoctrineTypes(): void
    {
        $this->registry->getConnection();
    }
}
