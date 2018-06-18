<?php

namespace Statamic\Addons\Spock;

use ReflectionClass;
use Statamic\API\Parse;
use Statamic\API\User as UserAPI;
use Statamic\Contracts\Data\DataEvent;
use Statamic\Extend\Listener;
use Symfony\Component\Process\Process;

class SpockListener extends Listener
{
    /**
     * Spock has no trouble listening for these events with those ears.
     *
     * @var array
     */
    public $events = [
        \Statamic\Events\Data\DataSaved::class => 'run',
        \Statamic\Events\Data\DataDeleted::class => 'run',
        \Statamic\Events\Data\AssetUploaded::class => 'run',
        \Statamic\Events\Data\AssetDeleted::class => 'run',
        \Statamic\Events\Data\AssetContainerSaved::class => 'run',
        \Statamic\Events\Data\SubmissionSaved::class => 'run',
    ];

    /**
     * @var DataEvent
     */
    private $event;

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

        $data['affected_paths'] = $this->event->affectedPaths();
        $data['user'] = UserAPI::getCurrent()->toArray();
        $data['listened_event'] = (new ReflectionClass($this->event))->getShortName();

        return collect($this->getConfig('commands', []))->map(function ($command) use ($data) {
            return Parse::template($command, $data);
        })->implode('; ');
    }
}
