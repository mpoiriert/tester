<?php

namespace Draw\Component\Tester\Tests\Command;

use Draw\Component\Tester\Application\CommandTestTrait;
use Draw\Component\Tester\Command\TestsCoverageCheckCommand;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @covers \Draw\Component\Tester\Command\TestsCoverageCheckCommand
 */
class TestsCoverageCheckCommandTest extends TestCase
{
    use CommandTestTrait;

    private const FIXTURES_FILE = __DIR__.'/fixtures/coverage_example.xml';

    /**
     * This is the computed % from the fixture files.
     */
    private const COMPUTED_PERCENTAGE = 46.46;

    public function createCommand(): Command
    {
        return new TestsCoverageCheckCommand();
    }

    public function getCommandName(): string
    {
        return 'draw:tester:coverage-check';
    }

    public function getCommandDescription(): string
    {
        return 'Check the code coverage by the tests';
    }

    public function provideTestArgument(): iterable
    {
        yield [
            'clover-xlm-file-path',
            InputArgument::REQUIRED,
            'Clover report file path',
        ];

        yield [
            'coverage',
            InputArgument::REQUIRED,
            'Required coverage in percentage',
        ];
    }

    public function provideTestOption(): iterable
    {
        return [];
    }

    public function testFileNotFound(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid input file provided');

        $this->executeCommand(__DIR__.'/test.xml', 50);
    }

    public function testInvalidPercentage(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid coverage percentage value');

        $this->executeCommand(static::FIXTURES_FILE, '');
    }

    public function testCodeNotCoveredEnough(): void
    {
        $commandTester = $this->executeCommand(static::FIXTURES_FILE, $against = 50);

        $this->assertSame(1, $commandTester->getStatusCode());

        $display = $commandTester->getDisplay();

        $lines = [
            '[ERROR] Code coverage is '.static::COMPUTED_PERCENTAGE.'%, which is below the accepted '.$against.'%',
        ];

        foreach ($lines as $line) {
            $this->assertStringContainsString(
                $line,
                $display
            );
        }
    }

    public function testCodeCoveredEnough(): void
    {
        $commandTester = $this->executeCommand(static::FIXTURES_FILE, $against = 40);

        $this->assertSame(0, $commandTester->getStatusCode());

        $display = $commandTester->getDisplay();

        $lines = [
            'Automation test coverage check',
            '[NOTE] Coverage threshold: '.$against,
            '[NOTE] Against file:',
            '[OK] Code coverage is '.static::COMPUTED_PERCENTAGE.'%',
        ];

        foreach ($lines as $line) {
            $this->assertStringContainsString(
                $line,
                $display
            );
        }
    }

    private function executeCommand($xlm, $coverage): CommandTester
    {
        $this->execute([
            'clover-xlm-file-path' => $xlm,
            'coverage' => $coverage,
        ]);

        return $this->commandTester;
    }
}