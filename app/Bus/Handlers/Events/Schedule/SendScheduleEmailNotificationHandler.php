<?php

/*
 * This file is part of Cachet.
 *
 * (c) Alt Three Services Limited
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CachetHQ\Cachet\Bus\Handlers\Events\Schedule;

use CachetHQ\Cachet\Bus\Events\Schedule\ScheduleEventInterface;
use CachetHQ\Cachet\Models\Subscriber;
use CachetHQ\Cachet\Notifications\Schedule\NewScheduleNotification;
use Illuminate\Contracts\Notifications\Dispatcher;

/**
 * This is the send schedule event notification handler.
 *
 * @author James Brooks <james@alt-three.com>
 */
class SendScheduleEmailNotificationHandler
{
    /**
     * The subscriber instance.
     *
     * @var \CachetHQ\Cachet\Models\Subscriber
     */
    protected $subscriber;

    /**
     * Create a new send schedule email notification handler.
     *
     * @param \CachetHQ\Cachet\Models\Subscriber $subscriber
     *
     * @return void
     */
    public function __construct(Subscriber $subscriber)
    {
        $this->subscriber = $subscriber;
    }

    /**
     * Handle the event.
     *
     * @param \CachetHQ\Cachet\Bus\Events\Schedule\ScheduleEventInterface $event
     *
     * @return void
     */
    public function handle(ScheduleEventInterface $event)
    {
        $schedule = $event->schedule;
        if ($schedule->notify) {
            // First notify all global subscribers.
            $this->subscriber->isVerified()->isGlobal()->get()->each(function ($subscriber) use ($schedule) {
                $subscriber->notify(new NewScheduleNotification($schedule));
            });
        }

        if ($schedule->notify_nh_clients) {
            app(Dispatcher::class)->send(new Subscriber(), new NewScheduleNotification($schedule));
        }
    }
}
