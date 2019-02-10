<?php

namespace Scheb\InspectionsConverter\Test\Cli;

use Scheb\Inspection\Converter\Cli\Command;
use Scheb\Inspection\Converter\Test\TestCase;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\BufferedOutput;

class CommandTest extends TestCase
{
    private const PROJECT_PATH = '/project/path';
    private const INSPECTIONS_DIR = __DIR__.'/../_inspections';
    private const OUTPUT_FILE = __DIR__.'/../_output/checkstyle.xml';
    private const EXPECTED_FILE = __DIR__.'/../_output/expected.xml';

    protected function setUp()
    {
        $this->clean();
    }

    protected function tearDown()
    {
        $this->clean();
    }

    /**
     * @test
     * @covers \Scheb\Inspection\Converter\Cli\Command
     */
    public function execute_inspectionsAndOutputGiven_createCheckstyleFile(): void
    {
        $this->runCommand();
        $this->assertCheckstyle();
    }

    private function runCommand(): void
    {
        $input = $this->createMock(InputInterface::class);
        $output = new BufferedOutput();

        $input
            ->expects($this->any())
            ->method('getArgument')
            ->willReturnMap([
                [Command::ARG_INSPECTIONS_FOLDER, self::INSPECTIONS_DIR],
                [Command::ARG_CHECKSTYLE_OUTPUT_FILE, self::OUTPUT_FILE],
            ]);

        $input
            ->expects($this->any())
            ->method('getOption')
            ->willReturnMap([
                [Command::OPT_PROJECT_ROOT, self::PROJECT_PATH],
                [Command::OPT_IGNORE_INSPECTION, ['ig[n].re', 'Ig[n].re']],
                [Command::OPT_IGNORE_FILE, ['ig[n].re', 'Ig[n].re']],
                [Command::OPT_IGNORE_MESSAGE, ['ig[n].re', 'Ig[n].re']],
                [Command::OPT_IGNORE_SEVERITY, ['IGNORED']],
                [Command::OPT_MAP_SEVERITY, ['ERROR:error', 'WARNING:warning']],
                [Command::OPT_DEFAULT_SEVERITY, 'default'],
            ]);

        $command = new Command();
        $command->run($input, $output);
    }

    private function assertCheckstyle(): void
    {
        $this->assertFileExists(self::OUTPUT_FILE);
        $this->assertFileEquals(self::EXPECTED_FILE, self::OUTPUT_FILE);
    }

    private function clean(): void
    {
        if (file_exists(self::OUTPUT_FILE)) {
            unlink(self::OUTPUT_FILE);
        }
    }
}
