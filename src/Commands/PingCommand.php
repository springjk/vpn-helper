<?php
namespace Springjk\Commands;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PingCommand extends Base
{
    protected function configure()
    {
        $this
            ->setName('ping')
            ->setDescription('Exec ping command test vpn servers');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $servers = $this->system->getServers();

        $this->system->pingTest($servers);

    }
}