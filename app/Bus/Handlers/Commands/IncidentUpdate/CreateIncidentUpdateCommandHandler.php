<?php

/*
 * This file is part of Cachet.
 *
 * (c) Alt Three Services Limited
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CachetHQ\Cachet\Bus\Handlers\Commands\IncidentUpdate;

use CachetHQ\Cachet\Bus\Commands\Incident\UpdateIncidentCommand;
use CachetHQ\Cachet\Bus\Commands\IncidentUpdate\CreateIncidentUpdateCommand;
use CachetHQ\Cachet\Bus\Events\IncidentUpdate\IncidentUpdateWasReportedEvent;
use CachetHQ\Cachet\Models\Component;
use CachetHQ\Cachet\Models\IncidentUpdate;
use Illuminate\Contracts\Auth\Guard;
use Twig\Environment as Twig_Environment;
use Twig\Loader\ArrayLoader as Twig_Loader_Array;

/**
 * This is the report incident update command handler.
 *
 * @author James Brooks <james@alt-three.com>
 */
class CreateIncidentUpdateCommandHandler
{
    /**
     * The authentication guard instance.
     *
     * @var \Illuminate\Contracts\Auth\Guard
     */
    protected $auth;

    /**
     * Create a new report incident update command handler instance.
     *
     * @param \Illuminate\Contracts\Auth\Guard $auth
     *
     * @return void
     */
    public function __construct(Guard $auth)
    {
        $this->auth = $auth;
    }

    /**
     * Handle the report incident command.
     *
     * @param \CachetHQ\Cachet\Bus\Commands\IncidentUpdate\CreateIncidentUpdateCommand $command
     *
     * @return \CachetHQ\Cachet\Models\IncidentUpdate
     */
    public function handle(CreateIncidentUpdateCommand $command)
    {
        if (! empty($command->message)) {
            $incident = $command->incident;

            $command->message = twig_parse($command->message, [
                'name'              => $incident->name,
                'status'            => trans('cachet.incidents.status')[$command->status],
                'visible'           => $incident->visible,
                'stickied'          => $incident->stickied,
                'occurred_at'       => $incident->occurred_at,
                'components'        => Component::find($command->components) ?: null,
                'component_status'  => trans('cachet.components.status')[$command->component_status],
            ]);
        }

        $data = [
            'incident_id' => $command->incident->id,
            'status'      => $command->status,
            'message'     => $command->message,
            'user_id'     => $command->user->id,
        ];

        // Create the incident update.
        $update = IncidentUpdate::create($data);

        // Update the original incident with the new status.
        execute(new UpdateIncidentCommand(
            $command->incident,
            null,
            $command->status,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            []
        ));

        if (! empty($command->components)) {
            Component::whereIn('id', $command->components)
                ->update(['status' => $command->component_status]);
        }

        event(new IncidentUpdateWasReportedEvent($this->auth->user(), $update));

        return $update;
    }
}
