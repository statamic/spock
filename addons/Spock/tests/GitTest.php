<?php

namespace Statamic\Addons\Spock;

use Mockery;
use Illuminate\Contracts\Logging\Log;
use Statamic\Contracts\Data\Users\User;
use Symfony\Component\Process\Process as SymfonyProcess;

class GitTest extends \PHPUnit_Framework_TestCase
{
    /** @test */
    function compiles_commands()
    {
        $user = Mockery::mock(User::class);
        $user->shouldReceive('username')->andReturn('johnsmith');

        $git = new Git([], new DataSaved, $user);

        $this->assertEquals([
            'git add one.txt',
            'git add two.txt',
            "git commit -m 'Data saved by johnsmith'", # Action is the "pretty" version of the class name.
        ], $git->commands());
    }

    /** @test */
    function commit_message_from_unauthenticated_user_contains_no_username()
    {
        $git = new Git([], new DataSaved, null);

        $this->assertEquals([
            'git add one.txt',
            'git add two.txt',
            "git commit -m 'Data saved'",
        ], $git->commands());
    }

    /** @test */
    function git_push_gets_appended_if_specified_in_config()
    {
        $user = Mockery::mock(User::class);
        $user->shouldReceive('username')->andReturn('johnsmith');

        $git = new Git(['git_push' => true], new DataSaved, $user);

        $this->assertEquals([
            'git add one.txt',
            'git add two.txt',
            "git commit -m 'Data saved by johnsmith'",
            'git push',
        ], $git->commands());
    }

    /** @test */
    function it_adds_commands_before_if_specified_in_config()
    {
        $user = Mockery::mock(User::class);
        $user->shouldReceive('username')->andReturn('johnsmith');

        $git = new Git([
            'commands_before' => ['echo one', 'echo two'],
        ], new DataSaved, $user);

        $this->assertEquals([
            'echo one',
            'echo two',
            'git add one.txt',
            'git add two.txt',
            "git commit -m 'Data saved by johnsmith'",
        ], $git->commands());
    }

    /** @test */
    function it_adds_commands_after_if_specified_in_config()
    {
        $user = Mockery::mock(User::class);
        $user->shouldReceive('username')->andReturn('johnsmith');

        $git = new Git([
            'commands_after' => ['echo one', 'echo two'],
        ], new DataSaved, $user);

        $this->assertEquals([
            'git add one.txt',
            'git add two.txt',
            "git commit -m 'Data saved by johnsmith'",
            'echo one',
            'echo two',
        ], $git->commands());
    }
}

class DataSaved
{
    public function affectedPaths()
    {
        return ['one.txt', 'two.txt'];
    }
}
