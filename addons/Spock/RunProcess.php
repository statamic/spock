<?php

namespace Statamic\Addons\Spock;

use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldQueue;
use Symfony\Component\Process\Exception\ProcessFailedException;

class RunProcess implements ShouldQueue, SelfHandling
{
    protected $command;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Process $command)
    {
        $this->command = $command;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $this->command->run();
        } catch (ProcessFailedException $e) {
            $this->logFailedCommand($this->command, $e);
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
