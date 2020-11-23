<?php

/*
 * This file is part of Cachet.
 *
 * (c) Alt Three Services Limited
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CachetHQ\Cachet\Bus\Handlers\Commands\Incident;

use CachetHQ\Cachet\Bus\Commands\Component\UpdateComponentCommand;
use CachetHQ\Cachet\Bus\Commands\Incident\CreateIncidentCommand;
use CachetHQ\Cachet\Bus\Events\Incident\IncidentWasCreatedEvent;
use CachetHQ\Cachet\Bus\Exceptions\Incident\InvalidIncidentTimestampException;
use CachetHQ\Cachet\Bus\Handlers\Traits\StoresMeta;
use CachetHQ\Cachet\Models\Component;
use CachetHQ\Cachet\Models\Incident;
use CachetHQ\Cachet\Models\IncidentComponent;
use CachetHQ\Cachet\Models\IncidentTemplate;
use CachetHQ\Cachet\Models\Meta;
use CachetHQ\Cachet\Services\Dates\DateFactory;
use Carbon\Carbon;
use Illuminate\Contracts\Auth\Guard;
use Twig\Environment as Twig_Environment;
use Twig\Loader\ArrayLoader as Twig_Loader_Array;

/**
 * This is the create incident command handler.
 *
 * @author James Brooks <james@alt-three.com>
 */
class CreateIncidentCommandHandler
{
    use StoresMeta;

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
     * Create a new create incident command handler instance.
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
     * Handle the create incident command.
     *
     * @param \CachetHQ\Cachet\Bus\Commands\Incident\CreateIncidentCommand $command
     *
     * @return \CachetHQ\Cachet\Models\Incident
     */
    public function handle(CreateIncidentCommand $command)
    {
        if (! empty($command->message)) {
            $command->message = twig_parse($command->message, [
                'name'              => $command->name,
                'status'            => trans('cachet.incidents.status')[$command->status],
                'visible'           => $command->visible,
                'notify'            => $command->notify,
                'notify_nh_clients' => $command->notify_nh_clients,
                'stickied'          => $command->stickied,
                'occurred_at'       => $command->occurred_at,
                'components'        => Component::find($command->components) ?: null,
                'component_status'  => trans('cachet.components.status')[$command->component_status],
            ]);
        }

        $data = [
            'user_id'           => $this->auth->user()->id,
            'name'              => $command->name,
            'status'            => $command->status,
            'visible'           => $command->visible,
            'stickied'          => $command->stickied,
            'notify'            => $command->notify,
            'notify_nh_clients' => $command->notify_nh_clients,
            'message'           => $command->message,
        ];

        // The incident occurred at a different time.
        if ($occurredAt = $command->occurred_at) {
            if ($date = $this->dates->create('Y-m-d H:i', $occurredAt)) {
                $data['occurred_at'] = $date;
            } else {
                throw new InvalidIncidentTimestampException("Unable to pass timestamp {$occurredAt}");
            }
        } else {
            $data['occurred_at'] = Carbon::now();
        }

        // Create the incident
        $incident = Incident::create($data);

        // Link with the component.
        if ($command->components) {
            Component::whereIn('id', $command->components)
                ->update(['status' => $command->component_status]);

            foreach ($command->components as $componentId) {
                IncidentComponent::create([
                    'incident_id'  => $incident->id,
                    'component_id' => $componentId,
                    'status_id'    => 0
                ]);
            }
        }

        // Store any meta?
        if ($command->meta) {
            $this->storeMeta($command->meta, 'incidents', $incident->id);
        }

        event(new IncidentWasCreatedEvent($this->auth->user(), $incident));

        return $incident;
    }

    /**
     * Compiles an incident template into an incident message.
     *
     * @param \CachetHQ\Cachet\Models\IncidentTemplate                     $template
     * @param \CachetHQ\Cachet\Bus\Commands\Incident\CreateIncidentCommand $command
     *
     * @return string
     */
    protected function parseTemplate(IncidentTemplate $template, CreateIncidentCommand $command)
    {
        $env = new Twig_Environment(new Twig_Loader_Array([]));
        $template = $env->createTemplate($template->template);

        $vars = array_merge($command->template_vars, [
            'incident' => [
                'name'             => $command->name,
                'status'           => $command->status,
                'message'          => $command->message,
                'visible'          => $command->visible,
                'notify'           => $command->notify,
                'stickied'         => $command->stickied,
                'occurred_at'      => $command->occurred_at,
                'component'        => Component::find($command->component_id) ?: null,
                'component_status' => $command->component_status,
            ],
        ]);

        return $template->render($vars);
    }
}
