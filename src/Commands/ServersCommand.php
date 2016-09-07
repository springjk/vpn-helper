<?php
namespace Springjk\Commands;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ServersCommand extends Base
{
    protected function configure()
    {
        $this
            ->setName('servers')
            ->setDescription('Displays local vpn server list');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $servers = $this->system->getServers();

        $this->showTable($servers, $output);
    }
}