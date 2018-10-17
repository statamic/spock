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

        foreach ($this->event->affectedPaths() as $path) {
            $commands[] = "git add {$path}";
        }

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
            $parts[] = sprintf('-c "user.name=%s"', $username);
        }

        if ($email = array_get($this->config, 'git_email')) {
            $parts[] = sprintf('-c "user.email=%s"', $email);
        }

        $message = 'commit -m "' . $this->label();
        $message .= $this->user ? ' by ' . $this->user->username() : '';
        $message .= '"';
        $parts[] = $message;

        return join(' ', $parts);
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
