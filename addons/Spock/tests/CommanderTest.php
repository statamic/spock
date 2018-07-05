<?php

namespace Statamic\Addons\Spock;

use Mockery;
use Illuminate\Contracts\Logging\Log;
use Symfony\Component\Process\Process as SymfonyProcess;
use Symfony\Component\Process\Exception\ProcessFailedException;

class CommanderTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->log = Mockery::spy(Log::class);

        $this->commander = (new Commander($this->log))
            ->config(['environments' => ['production']])
            ->environment('production');
    }

    public function tearDown()
    {
        Mockery::close();
    }

    /** @test */
    function only_runs_commands_for_whitelisted_environments()
    {
        $this->assertTrue($this->commander->shouldRunCommands());

        $this->commander
            ->config(['environments' => ['one', 'two']])
            ->environment('three');

        $this->assertFalse($this->commander->shouldRunCommands());
    }

    /** @test */
    function does_not_run_command_if_event_is_ignored()
    {
        $this->commander->environment('production')->config([
            'environments' => ['production'],
            'ignore_events' => [ExampleIgnoredEvent::class]
        ]);

        $this->assertTrue($this->commander->shouldRunCommands());

        $this->commander->event(new ExampleEvent);
        $this->assertTrue($this->commander->shouldRunCommands());

        $this->commander->event(new ExampleIgnoredEvent);
        $this->assertFalse($this->commander->shouldRunCommands());
    }

    /** @test */
    function command_strings_are_converted_to_objects()
    {
        $commands = ['echo one', 'echo two'];

        $this->commander->setCommands($commands);

        $this->assertEquals([
            new Process('echo one'),
            new Process('echo two')
        ], $this->commander->commands());
    }

    /** @test */
    function a_command_as_a_string_is_converted_to_an_array()
    {
        $this->commander->setCommands('string');

        $this->assertEquals([
            new Process('string'),
        ], $this->commander->commands());
    }

    /** @test */
    function commands_are_run()
    {
        $this->commander->setCommands([
            new Process('echo a command that will always work.')
        ])->handle();

        $this->log->shouldNotReceive('error');
    }

    /** @test */
    function erroring_commands_are_logged()
    {
        $erroringProcess = Mockery::mock(Process::class);
        $genericException = new \Exception('A generic exception');
        $erroringProcess->shouldReceive('run')->andThrow($genericException);

        $this->commander->setCommands([$erroringProcess])->handle();

        $this->log->shouldHaveReceived('error')->with($genericException);
    }

    /** @test */
    function failed_commands_are_logged()
    {
        $failingProcess = Mockery::mock(Process::class);
        $command = 'echo "some output"; nonexistingcommandIhopeneversomeonewouldnameacommandlikethis';
        $process = new SymfonyProcess($command);
        $process->run();
        $e = new ProcessFailedException($process);
        $failingProcess->shouldReceive('run')->andThrow($e);
        $failingProcess->shouldReceive('command')->andReturn($command);

        $this->commander->setCommands([$failingProcess])->handle();

        $this->log->shouldHaveReceived('error')->with(Mockery::on(function ($argument) use ($process) {
            return str_contains($argument, trim($process->getOutput()))
                && str_contains($argument, trim($process->getErrorOutput()));
        }));
    }

    /** @test */
    function the_literal_string_no_output_is_shown_if_theres_no_output()
    {
        $failingProcess = Mockery::mock(Process::class);
        $command = 'nonexistingcommandIhopeneversomeonewouldnameacommandlikethis';
        $process = new SymfonyProcess($command);
        $process->run();
        $e = new ProcessFailedException($process);
        $failingProcess->shouldReceive('run')->andThrow($e);
        $failingProcess->shouldReceive('command')->andReturn($command);

        $this->commander->setCommands([$failingProcess])->handle();

        $this->log->shouldHaveReceived('error')->with(Mockery::on(function ($argument) use ($process) {
            return str_contains($argument, 'Output: No output')
                && str_contains($argument, trim($process->getErrorOutput()));
        }));
    }

    /** @test */
    function the_literal_string_no_error_is_shown_if_theres_no_error()
    {
        $failingProcess = Mockery::mock(Process::class);
        $command = '(echo "some output"; exit 1)';
        $process = new SymfonyProcess($command);
        $process->run();
        $e = new ProcessFailedException($process);
        $failingProcess->shouldReceive('run')->andThrow($e);
        $failingProcess->shouldReceive('command')->andReturn($command);

        $this->commander->setCommands([$failingProcess])->handle();

        $this->log->shouldHaveReceived('error')->with(Mockery::on(function ($argument) use ($process) {
            return str_contains($argument, trim($process->getOutput()))
                && str_contains($argument, 'Error: No error');
        }));
    }

    /** @test */
    function commands_can_be_a_closure()
    {
        $this->commander->event(new class {
            public function foo() {
                return 'bar';
            }
        });

        $this->commander->setCommands(function ($commander) {
            return [
                'hardcoded command',
                'dynamic command ' . $commander->event()->foo()
            ];
        });

        $commands = $this->commander->commands();

        $this->assertEquals([
            new Process('hardcoded command'),
            new Process('dynamic command bar'),
        ], $commands);
    }
}

class ExampleEvent
{
    //
}

class ExampleIgnoredEvent
{
    //
}
