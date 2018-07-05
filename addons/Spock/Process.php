<?php

namespace Statamic\Addons\Spock;

use Symfony\Component\Process\Process as SymfonyProcess;
use Symfony\Component\Process\Exception\ProcessFailedException;

class Process
{
    protected $command;

    /**
     * @param string $command
     */
    public function __construct($command)
    {
        $this->command = $command;
    }

    /**
     * Run the command.
     *
     * @throws ProcessFailedException
     */
    public function run()
    {
        (new SymfonyProcess($this->command, BASE))->mustRun();
    }

    /**
     * Get the command line to be run.
     *
     * @return string
     */
    public function command()
    {
        return $this->command;
    }
}
