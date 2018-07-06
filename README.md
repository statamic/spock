# Spock for Statamic ![Statamic 2.10](https://img.shields.io/badge/statamic-2.10-blue.svg?style=flat-square)

Perform commands when content has been published or changed.

This addon essentially just listens for an event, and dispatches commands. Who better to listen and command than a Starship Commander with large ears?

Its primary use is to automatically commit and push changes in production, but it can do anything your command line can.

## Documentation

Read it on the [Statamic Marketplace](https://statamic.com/marketplace/addons/spock/docs) or contribute to it [here on GitHub](DOCUMENTATION.md).

## Requirements

Statamic 2.10 is required.  If you intend to run Spock on an earlier version of Statamic, please checkout Spock's [v1 branch](https://github.com/statamic/spock/tree/v1).

## Developing

You can use [Kessel Run](https://github.com/jesseleite/kessel-run) to copy files to your Statamic installation, and run `php please test:addons` to run Spock's tests.
