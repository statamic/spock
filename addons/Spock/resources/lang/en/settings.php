<?php

$env = app()->environment();

return [
    'environments' => 'Environments',
    'environments_instruct' => 'Spock will only run on the specified environments. The current environment is `'. app()->environment() . '`',

    'git_push' => 'Git Push',
    'git_push_instruct' => 'Whether Spock should perform a `git push` after the default git commands.',

    'commands_before' => 'Commands Before',
    'commands_before_instruct' => 'Commands to be run before the default git commands have been executed.',

    'commands_after' => 'Commands After',
    'commands_after_instruct' => 'Commands to be run after the default git commands have been executed.',

    'ignore_events' => 'Ignore Events',
    'ignore_events_instruct' => 'By default, Spock listens for all data-related events. You may choose to ignore specific ones.<br>Enter the full PHP class name, eg. `Statamic\Events\Data\UserSaved`',
];
