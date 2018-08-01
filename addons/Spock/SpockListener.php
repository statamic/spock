<?php

namespace Statamic\Addons\Spock;

use Statamic\API\User;
use Statamic\Extend\Listener;
use Statamic\Contracts\Data\DataEvent;

class SpockListener extends Listener
{
    /**
     * Spock has no trouble listening for these events with those ears.
     *
     * @var array
     */
    public $events = [
        \Statamic\Events\Data\AddonSettingsSaved::class => 'run',
        \Statamic\Events\Data\AssetUploaded::class => 'run',
        \Statamic\Events\Data\AssetMoved::class => 'run',
        \Statamic\Events\Data\AssetDeleted::class => 'run',
        \Statamic\Events\Data\AssetContainerSaved::class => 'run',
        \Statamic\Events\Data\AssetContainerDeleted::class => 'run',
        \Statamic\Events\Data\AssetFolderSaved::class => 'run',
        \Statamic\Events\Data\AssetFolderDeleted::class => 'run',
        \Statamic\Events\Data\CollectionSaved::class => 'run',
        \Statamic\Events\Data\CollectionDeleted::class => 'run',
        \Statamic\Events\Data\EntrySaved::class => 'run',
        \Statamic\Events\Data\EntryDeleted::class => 'run',
        \Statamic\Events\Data\FileUploaded::class => 'run',
        \Statamic\Events\Data\FieldsetSaved::class => 'run',
        \Statamic\Events\Data\FieldsetDeleted::class => 'run',
        \Statamic\Events\Data\FormSaved::class => 'run',
        \Statamic\Events\Data\GlobalsSaved::class => 'run',
        \Statamic\Events\Data\GlobalsDeleted::class => 'run',
        \Statamic\Events\Data\PageSaved::class => 'run',
        \Statamic\Events\Data\PageDeleted::class => 'run',
        \Statamic\Events\Data\PagesMoved::class => 'run',
        \Statamic\Events\Data\RoleSaved::class => 'run',
        \Statamic\Events\Data\RoleDeleted::class => 'run',
        \Statamic\Events\Data\SettingsSaved::class => 'run',
        \Statamic\Events\Data\SubmissionSaved::class => 'run',
        \Statamic\Events\Data\SubmissionDeleted::class => 'run',
        \Statamic\Events\Data\TaxonomySaved::class => 'run',
        \Statamic\Events\Data\TaxonomyDeleted::class => 'run',
        \Statamic\Events\Data\TermSaved::class => 'run',
        \Statamic\Events\Data\TermDeleted::class => 'run',
        \Statamic\Events\Data\UserSaved::class => 'run',
        \Statamic\Events\Data\UserDeleted::class => 'run',
        \Statamic\Events\Data\UserGroupSaved::class => 'run',
        \Statamic\Events\Data\UserGroupDeleted::class => 'run',
    ];

    /**
     * Handle the event, run the command(s).
     *
     * @param DataEvent $event
     * @return void
     */
    public function run(DataEvent $event)
    {
        app('spock')
            ->event($event)
            ->user(User::getCurrent())
            ->handle();
    }
}
