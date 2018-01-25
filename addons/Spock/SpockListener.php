<?php

namespace Statamic\Addons\Spock;

use Statamic\API\File;
use Statamic\API\Parse;
use Statamic\Extend\Listener;
use Statamic\API\User as UserAPI;
use Symfony\Component\Process\Process;
use Statamic\Contracts\Data\Users\User;

class SpockListener extends Listener
{
    /**
     * @var \Statamic\Contracts\Data\Data
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
     * @param \Statamic\Contracts\Data\Data $data
     * @return void
     */
    public function run($data)
    {
        // Do nothing if we aren't supposed to run in this environment.
        if (! $this->environmentWhitelisted()) {
            return;
        }

        $this->data = $data;

        $process = new Process($commands = $this->commands(), BASE);

        // Log any exceptions when attempting to run the commands
        try {
            $process->run();
        } catch (\Exception $e) {
            \Log::error('Spock command hit an exception: ' . $commands);
            \Log::error($e->getMessage());
        }

        // If the process did not exit successfully log the details
        if ($process->getExitCode() != 0) {
            \Log::error(
                "Spock command exited unsuccessfully: ". PHP_EOL .
                $commands . PHP_EOL .
                $process->getErrorOutput() . PHP_EOL .
                $process->getOutput()
            );
        }
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
        $data = $this->data->toArray();
        $data['full_path'] = $this->getPathPrefix() . $this->data->path();
        $data['committer'] = UserAPI::getCurrent()->toArray();

        $commands = [];

        foreach ($this->getConfig('commands', []) as $command) {
            $commands[] = Parse::template($command, $data);
        }

        return join('; ', $commands);
    }

    /**
     * Get the prefix to the data's path.
     *
     * @return string
     */
    private function getPathPrefix()
    {
        $disk = $this->data instanceof User ? 'users' : 'content';

        return File::disk($disk)->filesystem()->getAdapter()->getPathPrefix();
    }
}
