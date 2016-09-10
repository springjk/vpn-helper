<?php
namespace Springjk\Vpn;

use Symfony\Component\Console\Application as BasicApplication;
use Springjk\Commands\RunCommand;
use Springjk\Commands\ServersCommand;

class Application extends BasicApplication
{
    public function __construct($name, $version)
    {
        parent::__construct($name, $version);

        $this->add(new RunCommand());

        $this->add(new ServersCommand());
    }
}