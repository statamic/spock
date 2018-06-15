# Spock for Statamic ![Statamic 2.0](https://img.shields.io/badge/statamic-2.0-blue.svg?style=flat-square)

Perform commands when content has been published or changed.

This addon essentially just listens for an event, and dispatches commands. Who better to listen and command than a Starship Commander with large ears?

Its primary use is to automatically commit and push changes in production, but it can do anything your command line can.

## Installation
1. Copy over the files into the `site` folder.
2. Update the `spock.yaml` `commands` array with a series of unix commands to run.
3. Ensure the `environments` array contains the environment(s) you want Spock commands in.

## Commands
- The `commands` array must be an array of unix commands.
- Make sure to surround your commands in quotes.
- Each command will have access to:
  - A `full_path` variable which will be the full path to the file that was just modified.
  - All the data in the content you've published. `{{ title }}`, `{{ slug }}`, etc.
  - A `committer` array which is the user that published the content. It contains all the user's data. `{{ committer:username }}`, etc.

## Whitelisting Environments
If you will be using the CP to publish content from dev and production, but only want the commands to be run on
production, you should make sure the `environments` array contains only `production`. Spock will do nothing
when its running in any other environments.

## Example
On publishing, we want to use git to commit the page that was just edited, then push it.

```
commands:
  - "git add {{ full_path }}"
  - "git commit -m '{{ url }} updated by {{ committer:username }}'"
  - "git push"
```
