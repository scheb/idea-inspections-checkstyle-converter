<?php

namespace Scheb\Inspection\Converter\Cli;

use Symfony\Component\Console\Application as AbstractApplication;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Application extends AbstractApplication
{
    public function __construct()
    {
        parent::__construct('cli', '');
    }

    protected function getCommandName(InputInterface $input)
    {
        return 'convert';
    }

    protected function getDefaultCommands()
    {
        $defaultCommands = AbstractApplication::getDefaultCommands();
        $defaultCommands[] = new Command();

        return $defaultCommands;
    }

    public function getDefinition()
    {
        $inputDefinition = AbstractApplication::getDefinition();
        $inputDefinition->setArguments();

        return $inputDefinition;
    }

    public function doRun(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('IDEA Inspections Checkstyle Converter');
        if (!$input->getFirstArgument()) {
            $input = new ArrayInput(['--help']);
        }
        AbstractApplication::doRun($input, $output);
    }
}
