<?php
namespace Springjk\Commands;

use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ConnectionCommand extends Base
{
    protected function configure()
    {
        $this
            ->setName('connection')
            ->addArgument('connection_name', InputArgument::REQUIRED, 'Your vpn server connection name in system')
            ->setDescription('Connection a vpn server by connection name');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $connection_name = $input->getArgument('connection_name');

        $io->block('Connection ' . $connection_name . '...');

        $connection_status = $this->system->connection($connection_name);

        if ($connection_status) {
            $io->success('Connection success.');
        } else {
            $io->error('Connection error.');
        }
    }
}