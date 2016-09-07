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

//        $servers = $vpn->getServers();
        $servers = $this->system->getServers();

        $this->system->pingTest($servers);

        $progress = new ProgressBar($output, count($servers));

        $progress->start();

        $show_progress = function () use ($progress) {
            $progress->advance(1);
        };

        $servers = $this->system->analysisPingResult($servers, $show_progress);

        $progress->finish();

        $servers = $this->sortArray($servers, 'avg');

        $this->showTable($servers, $output);


    }
}