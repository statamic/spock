## Installation
1. Copy the `addons/Spock` directory into `site/addons`.
2. In `site/settings/addons/default.yaml`, ensure the `environments` array contains the environment(s) you want Spock commands in. By default, commands are only run in `production`.

## Commands
Out of the box, Spock will perform a few git commands to stage, commit, and push any affected files.

Basically, it will do this:

``` bash
# Any `commands_before` will be run here
# ...

# Each affected file will be staged in a separate command
git add modified_file.md
git add another_modified_file.md
git add deleted_file.md

git commit -m "Data saved by bob"  # or Fieldset saved, Asset uploaded, etc...

git push  # this is opt-in

# Any `commands_after` will be run here
# ...
```

You may enable `git push`-ing in your `spock.yaml`.

``` yaml
git_push: true
```

You may add hardcoded commands before or after the git commands by adding `commands_before` and/or `commands_after` to your `spock.yaml`:

``` yaml
commands_before:
  - some-unix-command
commands_after:
  - another-unix-command
```

## Custom commands
The Git workflow Spock provides out of the box works fine for most people, but if you have special requirements, you may define your own set of commands. You can do this in a service provider like so:

``` php
class YourServiceProvider extends ServiceProvider
{
    public function boot()
    {
        // As a string, for a single command:
        app('spock')->setCommands('some-command');

        // As an array for basic commands:
        app('spock')->setCommands(['command one', 'command two']);

        // A closure that returns an array of commands.
        // The first argument will be an instance of the `Commander` class. 
        app('spock')->setCommands(function ($spock) {
            $paths = $spock->event()->affectedPaths();
            //
            return [ ];
        });
    }
}
```

## Whitelisting Environments
If you will be using the CP to publish content from dev and production, but only want the commands to be run on
production, you should make sure the `environments` array contains only `production`. Spock will do nothing
when its running in any other environments.
