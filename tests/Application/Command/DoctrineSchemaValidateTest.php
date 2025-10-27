<?php

declare(strict_types=1);

namespace PixelFederation\DoctrineGenericTypesBundle\Tests\Application\Command;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

final class DoctrineSchemaValidateTest extends KernelTestCase
{
    public function testDoctrineSchemaValidate(): void
    {
        self::bootKernel();

        $application = new Application(self::$kernel);

        $this->assertMappedEntities($application);
        $this->assertMappingBeforeSync($application);
        $this->syncDb($application);
        $this->assertMappingAfterSync($application);
    }

    private function syncDb(Application $application): void
    {
        $command = $application->find('doctrine:schema:update');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            '--dump-sql' => true,
        ]);
        $commandTester->assertCommandIsSuccessful();
        $output = $commandTester->getDisplay();
        self::assertSame(
            'CREATE TABLE foo (userId CHAR(36) NOT NULL, firstName VARCHAR(255) NOT NULL, lastName VARCHAR(255) NOT NULL, isActive BOOLEAN NOT NULL, isExpired BOOLEAN NOT NULL, amount INTEGER NOT NULL, count INTEGER NOT NULL, successRate DOUBLE PRECISION NOT NULL, age INTEGER NOT NULL, heightInCm INTEGER NOT NULL, price CLOB NOT NULL, PRIMARY KEY(userId));
CREATE INDEX is_active_idx ON foo (isActive);
CREATE UNIQUE INDEX name_unique_idx ON foo (firstName, lastName);
',
            $output,
        );
        $commandTester->execute([
            '--force' => true,
        ]);
        $commandTester->assertCommandIsSuccessful();
    }

    private function assertMappingBeforeSync(Application $application): void
    {
        $command = $application->find('doctrine:schema:validate');
        $commandTester = new CommandTester($command);
        $exitCode = $commandTester->execute([]);
        self::assertSame(Command::INVALID, $exitCode);
        $output = $commandTester->getDisplay();

        self::assertStringContainsString('The mapping files are correct.', $output);
        self::assertStringContainsString('[ERROR] The database schema is not in sync with the current mapping file.', $output);
    }

    private function assertMappingAfterSync(Application $application): void
    {
        $command = $application->find('doctrine:schema:validate');
        $commandTester = new CommandTester($command);
        $commandTester->execute([]);
        $commandTester->assertCommandIsSuccessful();
        $output = $commandTester->getDisplay();

        self::assertStringContainsString('The mapping files are correct.', $output);
        self::assertStringContainsString('The database schema is in sync with the mapping files.', $output);
    }

    private function assertMappedEntities(Application $application): void
    {
        $command = $application->find('doctrine:mapping:info');
        $commandTester = new CommandTester($command);
        $commandTester->execute([]);
        $commandTester->assertCommandIsSuccessful();
        $output = $commandTester->getDisplay();

        self::assertSame(
            ' Found 1 mapped entities:

 [OK]   PixelFederation\DoctrineGenericTypesBundle\Tests\TestApplication\Entity\Foo
',
            $output,
        );
    }
}
