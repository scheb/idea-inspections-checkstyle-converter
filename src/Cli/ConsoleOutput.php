<?php

namespace Scheb\InspectionConverter\Cli;

use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;

class ConsoleOutput
{
    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @var bool
     */
    private $isDebug;

    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
        $this->isDebug = $output->isDebug();
    }

    public function write(string $string): void
    {
        $this->output->write($string);
    }

    public function writeln(?string $string = null): void
    {
        $this->output->writeln($string ?? '');
    }

    public function debug($string): void
    {
        if ($this->isDebug) {
            $this->writeln($string);
        }
    }

    public function createProgressBar(int $width): ProgressBar
    {
        $progress = new ProgressBar($this->output, $width);
        $progress->setBarWidth(50);
        $progress->display();

        return $progress;
    }
}
