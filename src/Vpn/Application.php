<?php
namespace Springjk\Vpn;

use Symfony\Component\Console\Application as BasicApplication;
use Springjk\Commands\RunCommand;
use Springjk\Commands\ServersCommand;

class Application extends BasicApplication
{
    public function __construct($name = 'Vpn helper', $version = '@git-version@')
    {
        parent::__construct($name, $version);

        $this->add(new RunCommand());

        $this->add(new ServersCommand());
    }

    /**
     * @override
     */
    public function getLongVersion()
    {
        if (('@' . 'git-version@') !== $this->getVersion()) {
            return sprintf(
                '<info>%s</info> version <comment>%s</comment> build <comment>%s</comment>',
                $this->getName(),
                $this->getVersion(),
                '@git-commit-short@'
            );
        }

        return '<info>' . $this->getName() . '</info> (repo)';
    }
}