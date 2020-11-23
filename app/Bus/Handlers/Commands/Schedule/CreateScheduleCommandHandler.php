<?php

/*
 * This file is part of Cachet.
 *
 * (c) Alt Three Services Limited
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CachetHQ\Cachet\Bus\Handlers\Commands\Schedule;

use AltThree\Validator\ValidationException;
use CachetHQ\Cachet\Bus\Commands\Schedule\CreateScheduleCommand;
use CachetHQ\Cachet\Bus\Events\Schedule\ScheduleWasCreatedEvent;
use CachetHQ\Cachet\Models\Component;
use CachetHQ\Cachet\Models\Schedule;
use CachetHQ\Cachet\Models\ScheduleComponent;
use CachetHQ\Cachet\Services\Dates\DateFactory;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Support\MessageBag;
use InvalidArgumentException;
use Twig\Environment as Twig_Environment;
use Twig\Loader\ArrayLoader as Twig_Loader_Array;

/**
 * This is the create schedule command handler.
 *
 * @author James Brooks <james@alt-three.com>
 */
class CreateScheduleCommandHandler
{
    /**
     * The authentication guard instance.
     *
     * @var \Illuminate\Contracts\Auth\Guard
     */
    protected $auth;

    /**
     * The date factory instance.
     *
     * @var \CachetHQ\Cachet\Services\Dates\DateFactory
     */
    protected $dates;

    /**
     * Create a new update schedule command handler instance.
     *
     * @param \Illuminate\Contracts\Auth\Guard            $auth
     * @param \CachetHQ\Cachet\Services\Dates\DateFactory $dates
     *
     * @return void
     */
    public function __construct(Guard $auth, DateFactory $dates)
    {
        $this->auth = $auth;
        $this->dates = $dates;
    }

    /**
     * Handle the create schedule command.
     *
     * @param \CachetHQ\Cachet\Bus\Commands\Schedule\CreateScheduleCommand $command
     *
     * @return \CachetHQ\Cachet\Models\Schedule
     */
    public function handle(CreateScheduleCommand $command)
    {
        try {
            $schedule = Schedule::create($this->filter($command));

            if (! empty($command->components)) {
                foreach ($command->components as $componentId) {
                    ScheduleComponent::create([
                        'schedule_id'       => $schedule->id,
                        'component_id'      => $componentId,
                        'component_status'  => 0,
                    ]);
                }
            }

            event(new ScheduleWasCreatedEvent($this->auth->user(), $schedule));
        } catch (InvalidArgumentException $e) {
            throw new ValidationException(new MessageBag([$e->getMessage()]));
        }

        return $schedule;
    }

    /**
     * Filter the command data.
     *
     * @param \CachetHQ\Cachet\Bus\Commands\Schedule\CreateScheduleCommand $command
     *
     * @return array
     */
    protected function filter(CreateScheduleCommand $command)
    {
        if (! empty($command->message)) {
            $command->message = twig_parse($command->message, [
                'name'              => $command->name,
                'status'            => trans('cachet.schedules.status')[$command->status],
                'notify'            => $command->notify,
                'notify_nh_clients' => $command->notify_nh_clients,
                'scheduled_at'      => $command->scheduled_at,
                'completed_at'      => $command->completed_at,
                'components'        => Component::find($command->components) ?: null,
            ]);
        }

        $scheduledAt = $this->dates->create('Y-m-d H:i', $command->scheduled_at);

        if ($completedAt = $command->completed_at) {
            $completedAt = $this->dates->create('Y-m-d H:i', $command->completed_at);
        }

        $params = [
            'name'              => $command->name,
            'message'           => $command->message,
            'status'            => $command->status,
            'scheduled_at'      => $scheduledAt,
            'completed_at'      => $completedAt,
            'notify'            => $command->notify,
            'notify_nh_clients' => $command->notify_nh_clients,
        ];

        $availableParams = array_filter($params, function ($val) {
            return $val !== null && $val !== '';
        });

        return $availableParams;
    }
}
