<?php

namespace Orbitali\Http\Listeners;

class AuthEventSubscriber
{
    /**
     * Handle user login events.
     */
    public function onUserLogin($event)
    {
        $event->user->extras->last_login_ip = request()->ip();
    }

    /**
     * Handle user logout events.
     */
    public function onUserLogout($event)
    {

    }

    /**
     * Register the listeners for the subscriber.
     *
     * @param  \Illuminate\Events\Dispatcher $events
     */
    public function subscribe($events)
    {
        $events->listen(
            'Illuminate\Auth\Events\Login',
            self::class . '@onUserLogin'
        );

        $events->listen(
            'Illuminate\Auth\Events\Logout',
            self::class . '@onUserLogout'
        );
    }

}
