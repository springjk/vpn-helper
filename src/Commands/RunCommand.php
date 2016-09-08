<?php
namespace Springjk\Commands;

use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RunCommand extends Base
{
    protected function configure()
    {
        $this
            ->setName('run')
            ->setDescription('ping test all vpn server and connection the fastest one');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('read VPN server list from system configuration file...');

        $servers = $this->system->getServers();

        if (empty($servers)) {
            throw new \Exception('read VPN server list failed!');
        }

        $output->writeln('run ping...');

        $this->system->pingTest($servers);

        $progress = new ProgressBar($output, count($servers));

        $progress->start();

        $show_progress = function () use ($progress) {
            $progress->advance();
        };

        $servers = $this->system->analysisPingResult($servers, $show_progress);

        $progress->finish();

        $servers = $this->sortArray($servers, 'avg');

        $this->showTable($servers, $output);

        $fastest_vpn_connection_name = $servers[0]['name'];

        $fastest_vpn_info = 'the minimal delay line is：' . $fastest_vpn_connection_name . ' AVG: ' . $servers[0]['avg'];

        $output->writeln($fastest_vpn_info);

        $output->writeln('connection ' . $fastest_vpn_connection_name . '...');

        $this->system->connection($fastest_vpn_connection_name);

        $connection_status = $this->system->checkConnectionStatus($fastest_vpn_connection_name);

        if ($connection_status) {
            $output->writeln('<info>connection success.</info>');
        } else {
            $output->writeln('<error>connection error.</error>');
        }
    }
}