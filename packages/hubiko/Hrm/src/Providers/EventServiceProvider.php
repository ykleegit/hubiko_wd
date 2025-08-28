<?php

namespace Hubiko\Hrm\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as Provider;
use App\Events\CompanyMenuEvent;
use App\Events\CompanySettingEvent;
use App\Events\CompanySettingMenuEvent;
use App\Events\CreateUser;
use App\Events\DefaultData;
use App\Events\GivePermissionToRole;
use App\Events\UpdateUser;
use Hubiko\Assets\Events\CreateAssets;
use Hubiko\Assets\Events\UpdateAssets;
use Hubiko\Hrm\Listeners\CompanyMenuListener;
use Hubiko\Hrm\Listeners\CompanySettingListener;
use Hubiko\Hrm\Listeners\CompanySettingMenuListener;
use Hubiko\Hrm\Listeners\CreateAssetsLis;
use Hubiko\Hrm\Listeners\DataDefault;
use Hubiko\Hrm\Listeners\GiveRoleToPermission;
use Hubiko\Hrm\Listeners\UpdateAssetsLis;
use Hubiko\Hrm\Listeners\UserCreate;
use Hubiko\Hrm\Listeners\UserUpdate;

class EventServiceProvider extends Provider
{
    /**
     * Determine if events and listeners should be automatically discovered.
     *
     * @return bool
     */
    protected $listen = [
        CompanyMenuEvent::class => [
            CompanyMenuListener::class,
        ],
        CompanySettingEvent::class => [
            CompanySettingListener::class,
        ],
        CompanySettingMenuEvent::class => [
            CompanySettingMenuListener::class,
        ],
        CreateAssets::class => [
            CreateAssetsLis::class,
        ],
        UpdateAssets::class => [
            UpdateAssetsLis::class,
        ],
        CreateUser::class => [
            UserCreate::class
        ],
        UpdateUser::class => [
            UserUpdate::class
        ],
        DefaultData::class => [
            DataDefault::class
        ],
        GivePermissionToRole::class => [
            GiveRoleToPermission::class
        ],
    ];

    /**
     * Get the listener directories that should be used to discover events.
     *
     * @return array
     */
    protected function discoverEventsWithin()
    {
        return [
            __DIR__ . '/../Listeners',
        ];
    }
}
