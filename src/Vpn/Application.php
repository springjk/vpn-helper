<?php
namespace Springjk\Vpn;

use Springjk\Commands\PingCommand;
use Springjk\Commands\RunCommand;
use Symfony\Component\Console\Application as BasicApplication;
use Springjk\Commands\ServersCommand;

class Application extends BasicApplication
{
    public function __construct($name, $version)
    {
        parent::__construct($name, $version);

        $this->add(new RunCommand());

        $this->add(new ServersCommand());

        $this->add(new PingCommand());
    }
}