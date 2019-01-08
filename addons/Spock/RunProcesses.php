<?php

namespace Statamic\Addons\Spock;

use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldQueue;
use Symfony\Component\Process\Exception\ProcessFailedException;

class RunProcesses implements ShouldQueue, SelfHandling
{
    protected $commands;

    /**
     * Create a new job instance.
     *
     * @param array $commands
     */
    public function __construct(array $commands)
    {
        $this->commands = $commands;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        foreach ($this->commands as $command) {
            $this->run($command);
        }
    }

    /**
     * Run individual command.
     *
     * @param Process $command
     */
    public function run(Process $command)
    {
        try {
            $command->run();
        } catch (ProcessFailedException $e) {
            $this->logFailedCommand($command, $e);
        } catch (\Exception $e) {
            app('log')->error($e);
        }
    }

    /**
     * Log failed command.
     *
     * @param Process $command
     * @param mixed $e
     */
    protected function logFailedCommand(Process $command, $e)
    {
        $output = trim($e->getProcess()->getOutput());
        $output = $output == '' ? 'No output' : "\n$output\n";

        $error = trim($e->getProcess()->getErrorOutput());
        $error = $error == '' ? 'No error' : "\n$error";

        app('log')->error(vsprintf("Spock command exited unsuccessfully:\nCommand: %s\nOutput: %s\nError: %s", [
            $command->command(),
            $output,
            $error
        ]));
    }
}
