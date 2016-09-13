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

        $fastest_vpn_connection_name = $this->getFastestVpn();

        $io->section('Connection the fastest one server');

        $this->connection($fastest_vpn_connection_name);
    }

    public function getFastestVpn()
    {
        $ping_command = $this->getApplication()->get('ping');

        $fastest_vpn_connection_name = $ping_command->execute(new ArrayInput([]), new ConsoleOutput());

        return $fastest_vpn_connection_name;
    }

    public function connection($connection_name)
    {
        $connection_command = $this->getApplication()->get('connection');

        $input = new ArrayInput([
            'connection_name' => $connection_name
        ]);

        $output = new ConsoleOutput();

        $connection_command->run($input, $output);
    }
}