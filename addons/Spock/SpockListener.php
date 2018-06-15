<?php

namespace Statamic\Addons\Spock;

use Statamic\API\Parse;
use Statamic\API\User as UserAPI;
use Statamic\Contracts\Data\DataEvent;
use Statamic\Events\Data\AssetUploaded;
use Statamic\Events\Data\DataSaved;
use Statamic\Extend\Listener;
use Symfony\Component\Process\Process;

class SpockListener extends Listener
{
    /**
     * @var DataEvent
     */
    private $event;

    /**
     * Spock has no trouble listening for these events with those ears.
     *
     * @var array
     */
    public $events = [
        DataSaved::class => 'run',
        AssetUploaded::class => 'run',
    ];

    /**
     * Handle the event, run the command(s).
     *
     * @param DataEvent $event
     * @return void
     */
    public function run(DataEvent $event)
    {
        // Do nothing if we aren't supposed to run in this environment.
        if (! $this->environmentWhitelisted()) {
            return;
        }

        \Log::info('spock is running!'); // temporary!

        $this->event = $event;

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
     * Get the concat'ed commands.
     *
     * @return string
     */
    private function commands()
    {
        $data = $this->event->contextualData();

        $data['full_path'] = $this->event->affectedPaths()[0];
        $data['committer'] = UserAPI::getCurrent()->toArray();

        $commands = [];

        foreach ($this->getConfig('commands', []) as $command) {
            $commands[] = Parse::template($command, $data);
        }

        return \Log::info(join(';', $commands)); // temporary!

        return join('; ', $commands);
    }
}
