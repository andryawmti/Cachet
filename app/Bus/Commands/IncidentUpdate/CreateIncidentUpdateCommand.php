<?php

/*
 * This file is part of Cachet.
 *
 * (c) Alt Three Services Limited
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CachetHQ\Cachet\Bus\Commands\IncidentUpdate;

use CachetHQ\Cachet\Models\Incident;
use CachetHQ\Cachet\Models\User;

/**
 * This is the report incident update command.
 *
 * @author James Brooks <james@alt-three.com>
 */
final class CreateIncidentUpdateCommand
{
    /**
     * The incident.
     *
     * @var \CachetHQ\Cachet\Models\Incident
     */
    public $incident;

    /**
     * The incident status.
     *
     * @var int
     */
    public $status;

    /**
     * The incident message.
     *
     * @var string
     */
    public $message;
    /**
     * The incident component.
     *
     * @var array
     */
    public $components;

    /**
     * The component status.
     *
     * @var int
     */
    public $component_status;
    /**
     * The user.
     *
     * @var \CachetHQ\Cachet\Models\User
     */
    public $user;

    /**
     * The validation rules.
     *
     * @var string[]
     */
    public $rules = [
        'incident'         => 'required',
        'status'           => 'required|int|min:1|max:4',
        'message'          => 'required|string',
        'components'       => 'nullable|required_with:component_status|array',
        'component_status' => 'nullable|required_with:components|int|min:0|max:4',
        'user'             => 'required',
    ];

    /**
     * Create a new report incident update command instance.
     *
     * @param \CachetHQ\Cachet\Models\Incident $incident
     * @param string                           $status
     * @param string                           $message
     * @param \CachetHQ\Cachet\Models\User     $user
     *
     * @return void
     */
    public function __construct(Incident $incident, $status, $message, $components, $component_status, User $user)
    {
        $this->incident = $incident;
        $this->status = $status;
        $this->message = $message;
        $this->components = $components;
        $this->component_status = $component_status;
        $this->user = $user;
    }
}
