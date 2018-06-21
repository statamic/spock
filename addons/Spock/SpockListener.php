<?php

namespace Statamic\Addons\Spock;

use ReflectionClass;
use Statamic\API\Parse;
use Statamic\API\User;
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
        \Statamic\Events\Data\PageMoved::class => 'run',
        \Statamic\Events\Data\AssetUploaded::class => 'run',
        \Statamic\Events\Data\AssetMoved::class => 'run',
        \Statamic\Events\Data\AssetDeleted::class => 'run',
        \Statamic\Events\Data\AssetContainerSaved::class => 'run',
        \Statamic\Events\Data\AssetContainerDeleted::class => 'run',
        \Statamic\Events\Data\AssetFolderSaved::class => 'run',
        \Statamic\Events\Data\AssetFolderDeleted::class => 'run',
        \Statamic\Events\Data\FileUploaded::class => 'run',
        \Statamic\Events\Data\SubmissionSaved::class => 'run',
        \Statamic\Events\Data\CollectionDeleted::class => 'run',
        \Statamic\Events\Data\TaxonomyDeleted::class => 'run',
        \Statamic\Events\Data\FieldsetSaved::class => 'run',
        \Statamic\Events\Data\FieldsetDeleted::class => 'run',
        \Statamic\Events\Data\SettingsSaved::class => 'run',
        \Statamic\Events\Data\UserSaved::class => 'run',
        \Statamic\Events\Data\UserDeleted::class => 'run',
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

        // Store and log event.
        $this->event = $event;
        \Log::info('Spock is running on event: ' . get_class($event));

        // Setup command process.
        $process = new Process($commands = $this->commands(), BASE);

        // Attempt running command process.
        try {
            \Log::info('Spock is attempting the following commands: ' . $commands);
            $process->run();
        } catch (\Exception $e) {
            \Log::error(
                'Spock command threw an exception: ' . PHP_EOL .
                $e->getMessage()
            );
        }

        // Log if the process exited unsuccessfully.
        if ($process->getExitCode() != 0) {
            \Log::error(
                'Spock command exited unsuccessfully:' . PHP_EOL .
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
        $data['user'] = User::getCurrent()->toArray();
        $data['listened_event'] = (new ReflectionClass($this->event))->getShortName();

        return collect($this->getConfig('commands', []))->map(function ($command) use ($data) {
            return Parse::template($command, $data);
        })->implode('; ');
    }
}
