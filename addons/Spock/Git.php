<?php

namespace Statamic\Addons\Spock;

use ReflectionClass;
use Statamic\Contracts\Data\Users\User;

class Git
{
    protected $config;
    protected $event;
    protected $user;

    /**
     * @param array $config  The Spock config.
     * @param mixed $event   The event class that Spock listened for.
     * @param User  $user    The user that triggered the event.
     */
    public function __construct($config, $event, $user = null)
    {
        $this->config = $config;
        $this->event = $event;
        $this->user = $user;
    }

    /**
     * Get the commands to be executed.
     *
     * @return array
     */
    public function commands()
    {
        $commands = array_get($this->config, 'commands_before', []);

        $commands[] = "git add --all";

        $commands[] = $this->commitCommand();

        if (array_get($this->config, 'git_push')) {
            $commands[] = 'git push';
        }

        if ($after = array_get($this->config, 'commands_after', [])) {
            $commands = array_merge($commands, $after);
        }

        return $commands;
    }

    /**
     * Get the git commit command
     *
     * eg. The `git commit -m "The message"` bit.
     *
     * @return string
     */
    protected function commitCommand()
    {
        $parts = ['git'];

        if ($username = array_get($this->config, 'git_username')) {
            $parts[] = '-c';
            $parts[] = escapeshellarg('user.name=' . $username);
        }

        if ($email = array_get($this->config, 'git_email')) {
            $parts[] = '-c';
            $parts[] = escapeshellarg('user.email=' . $email);
        }

        $parts[] = 'commit';
        if ($this->user) {
            $parts[] = escapeshellarg($this->author());
        }
        $parts[] = '-m';
        $parts[] = escapeshellarg($this->commitMessage());
        return join(' ', $parts);
    }



    /**
     * Create a Git author parameter from the user's name and email address,
     * i.e, "--author=A U Thor <author@example.com>"
     */
    protected function author() {
        return sprintf('--author=%s <%s>', $this->user->name(), $this->user->email());
    }


    /**
     * Generate the commit message
     *
     * @return string
     */
    protected function commitMessage() {
        $msg = $this->label();
        if ($this->user) {
            $msg .= ' by ' . $this->user->username();
        }
        return $msg;
    }


    /**
     * Get the label of the class, which is the action name.
     *
     * eg. "Statamic\Events\Data\DataSaved" becomes "Data saved"
     *
     * @return string
     */
    protected function label()
    {
        $class = (new ReflectionClass($this->event))->getShortName();

        return ucfirst(str_replace('_', ' ', snake_case($class)));
    }
}
