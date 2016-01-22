# Spock
## Perform commands when content has been published.

> Who better to listen for a publish event than a commander of the enterprise with the biggest ears?

This addon is useful for automatically committing content.

## Installation
1. Copy over the files into the `site` folder.
2. Update the `spock.yaml` `commands` array with a series of unix commands to run.

## Commands
- The `commands` array must be an array of unix commands.
- Make sure to surround your commands in quotes.
- Each command will have access to:
  - A `full_path` variable which will be the full path to the file that was just modified.
  - All the data in the content you've published. `{{ title }}`, `{{ slug }}`, etc.
  - A `committer` array which is the user that published the content. It contains all the user's data. `{{ committer:username }}`, etc.

## Example
On publishing, we want to commit the page that was just edited, then push it.

```
commands:
  - "git add {{ full_path }}"
  - "git commit -m '{{ url }} updated by {{ committer:username }}'"
  - "git push"
```
