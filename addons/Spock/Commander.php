<?php

namespace Statamic\Addons\Spock;

use Illuminate\Contracts\Logging\Log;
use Statamic\Contracts\Data\Users\User;
use Symfony\Component\Process\Exception\ProcessFailedException;

class Commander
{
    protected $log;
    protected $user;
    protected $event;
    protected $config = [];
    protected $environment;
    protected $commands = [];

    /**
     * @param Log $log
     */
    public function __construct(Log $log)
    {
        $this->log = $log;
    }

    /**
     * Handle execution of the commands.
     *
     * @return void
     */
    public function handle()
    {
        if (!$this->shouldRunCommands()) {
            return;
        }

        foreach ($this->commands() as $command) {
            $this->run($command);
        }
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

    protected function isEnvironmentAllowed()
    {
        return in_array($this->environment, array_get($this->config, 'environments', []));
    }

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

    /**
     * Run a single command.
     *
     * @param Process $command
     * @return void
     */
    protected function run(Process $command)
    {
        try {
            $command->run();
        } catch (ProcessFailedException $e) {
            $this->logFailedCommand($command, $e);
        } catch (\Exception $e) {
            $this->log->error($e);
        }
    }

    protected function logFailedCommand($command, $e)
    {
        $output = trim($e->getProcess()->getOutput());
        $output = $output == '' ? 'No output' : "\n$output\n";

        $error = trim($e->getProcess()->getErrorOutput());
        $error = $error == '' ? 'No error' : "\n$error";

        $this->log->error(vsprintf("Spock command exited unsuccessfully:\nCommand: %s\nOutput: %s\nError: %s", [
            $command->command(),
            $output,
            $error
        ]));
    }
}
