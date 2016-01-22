<?php

namespace Statamic\Addons\Spock;

use Statamic\API\Path;
use Statamic\API\User;
use Statamic\API\Parse;
use Statamic\Extend\Listener;
use Symfony\Component\Process\Process;

class SpockListener extends Listener
{
    /**
     * @var Statamic\Contracts\Data\Data
     */
    private $data;

    /**
     * Spock has no trouble listening for these events with those ears.
     *
     * @var array
     */
    public $events = [
        'cp.published' => 'run'
    ];

    /**
     * Handle the event, run the command(s).
     *
     * @param Statamic\Contracts\Data\Data $data
     * @return void
     */
    public function run($data)
    {
        // Do nothing if we aren't supposed to run in this environment.
        if (! $this->environmentWhitelisted()) {
            return;
        }

        $this->data = $data;

        $process = new Process($this->commands());

        $process->run();
    }

    /**
     * Is the current environment whitelisted?
     *
     * @return bool
     */
    private function environmentWhitelisted()
    {
        return in_array(app()->environment(), $this->getConfig('environments', []));
    }

    /**
     * Get the concat'ed commands
     *
     * @return string
     */
    private function commands()
    {
        $full_path = Path::assemble(root_path(), $this->data->path());

        $data = $this->data->toArray();
        $data['full_path'] = $full_path;
        $data['committer'] = User::getCurrent()->toArray();

        $commands = [];

        foreach ($this->getConfig('commands', []) as $command) {
            $commands[] = Parse::template($command, $data);
        }

        return join('; ', $commands);
    }
}
