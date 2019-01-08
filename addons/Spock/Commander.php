<?php

namespace Statamic\Addons\Spock;

use Statamic\Contracts\Data\Users\User;
use Illuminate\Foundation\Bus\DispatchesJobs;

class Commander
{
    use DispatchesJobs;

    protected $user;
    protected $event;
    protected $config = [];
    protected $environment;
    protected $commands = [];

    /**
     * Handle execution of the commands.
     *
     * @return void
     */
    public function handle()
    {
        if (! $this->shouldRunCommands()) {
            return;
        }

        $this->dispatch(new RunProcesses($this->commands()));
    }

    /**
     * Set the environment.
     *
     * @param string $environment
     * @return self
     */
    public function environment($environment)
    {
        $this->environment = $environment;

        return $this;
    }

    /**
     * Get or set the event.
     *
     * @param string $event
     * @return self
     */
    public function event($event = null)
    {
        if (! $event) {
            return $this->event;
        }

        $this->event = $event;

        return $this;
    }

    /**
     * Set the user that triggered the event.
     *
     * @param User $user
     * @return self
     */
    public function user($user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Set the Spock config.
     *
     * @param array $config
     * @return self
     */
    public function config($config)
    {
        $this->config = $config;

        return $this;
    }

    /**
     * Whether the commands should be run.
     *
     * @return bool
     */
    public function shouldRunCommands()
    {
        return $this->isEnvironmentAllowed() && $this->isEventAllowed();
    }

    /**
     * Is environment allowed?
     *
     * @return bool
     */
    protected function isEnvironmentAllowed()
    {
        return in_array($this->environment, array_get($this->config, 'environments', []));
    }

    /**
     * Is event allowed?
     *
     * @return bool
     */
    protected function isEventAllowed()
    {
        return !in_array(get_class($this->event), array_get($this->config, 'ignore_events', []));
    }

    /**
     * Set the commands to be run.
     *
     * @param array $commands
     * @return self
     */
    public function setCommands($commands)
    {
        $this->commands = $commands;

        return $this;
    }

    /**
     * Get the commands to be processed.
     *
     * @return Process[]
     */
    public function commands()
    {
        $commands = $this->commands ?: $this->defaultCommands();

        if ($commands instanceof \Closure) {
            $commands = $commands($this);
        }

        if (is_string($commands)) {
            $commands = [$commands];
        }

        return array_map(function ($command) {
            return ($command instanceof Process) ? $command : new Process($command);
        }, $commands);
    }

    /**
     * Get the commands to be run if none have been specified.
     *
     * @return array
     */
    protected function defaultCommands()
    {
        return (new Git($this->config, $this->event, $this->user))->commands();
    }
}
