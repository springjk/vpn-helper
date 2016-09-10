<?php
namespace Springjk\Vpn;

use Symfony\Component\Console\Application as BasicApplication;
use Springjk\Commands\RunCommand;
use Springjk\Commands\ServersCommand;

class Application extends BasicApplication
{
    public function __construct()
    {
        parent::__construct();

        $this->add(new RunCommand());

        $this->add(new ServersCommand());

        $this->setName('vpn helper');
        $this->setVersion('1.0.0');
    }
}