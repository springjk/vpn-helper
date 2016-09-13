<?php
namespace Springjk\Commands;

use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RunCommand extends Base
{
    protected function configure()
    {
        $this
            ->setName('run')
            ->setDescription('Ping test all vpn servers and connection the fastest one');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $io = new SymfonyStyle($input, $output);

        $fastest_vpn_connection_name = $this->executeCommand('ping', new ArrayInput([]), new ConsoleOutput());

        $io->section('Connection the fastest one server');

        $input = new ArrayInput([
            'connection_name' => $fastest_vpn_connection_name
        ]);

        $output = new ConsoleOutput();

        $this->runCommand('connection', $input, $output);
    }
}