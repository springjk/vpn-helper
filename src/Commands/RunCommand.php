<?php
namespace Springjk\Commands;

use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
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
            ->setDescription('Ping test vpn server list and connection the fastest one');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $io = new SymfonyStyle($input, $output);

        $servers = $this->getServers($io);

        $io->section('Execute ping command test servers speed');

        $io->write('start ping...');

        $this->system->pingTest($servers);

        $io->progressStart(count($servers));

        $servers = $this->system->analysisPingResult($servers, function ($message) use ($io) {
            $io->progressAdvance();

            $io->write(' ' . $message);
        });

        $io->progressFinish();

        $servers = $this->sortArray($servers, 'avg');

        $this->showTable($servers, $output);

        $fastest_vpn_connection_name = $servers[0]['name'];

        $fastest_vpn_info = 'The minimal delay line is：' . $fastest_vpn_connection_name . ' AVG: ' . $servers[0]['avg'];

        $io->block($fastest_vpn_info);

        $io->section('Connection the fastest one server');

        $this->connection($fastest_vpn_connection_name);
    }

    public function getServers(SymfonyStyle $io)
    {
        $io->section('Read VPN server list');

        $servers_command = $this->getApplication()->get('servers');

        $servers = $servers_command->execute(new ArrayInput([]), new NullOutput());

        if (empty($servers)) {
            throw new \ErrorException('Read VPN server list failed!');
        } else {
            $io->block(sprintf('Get %d servers from system configuration file', count($servers)));
        }

        return $servers;
    }

    public function connection($connection_name)
    {
        $servers_command = $this->getApplication()->get('connection');

        $input = new ArrayInput([
            'connection_name' => $connection_name
        ]);

        $output = new ConsoleOutput();

        $servers_command->run($input, $output);
    }
}