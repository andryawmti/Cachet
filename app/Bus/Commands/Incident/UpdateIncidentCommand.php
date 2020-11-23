<?php

/*
 * This file is part of Cachet.
 *
 * (c) Alt Three Services Limited
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CachetHQ\Cachet\Bus\Commands\Incident;

use CachetHQ\Cachet\Models\Incident;

/**
 * This is the update incident command.
 *
 * @author James Brooks <james@alt-three.com>
 * @author Joseph Cohem <joe@alt-three.com>
 * @author Graham Campbell <graham@alt-three.com>
 */
final class UpdateIncidentCommand
{
    /**
     * The incident to update.
     *
     * @var \CachetHQ\Cachet\Models\Incident
     */
    public $incident;

    /**
     * The incident name.
     *
     * @var string
     */
    public $name;

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
     * The incident visibility.
     *
     * @var int
     */
    public $visible;

    /**
     * components
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
     * Whether to stick the incident on top.
     *
     * @var bool
     */
    public $stickied;

    /**
     * The timestamp that the incident occurred at.
     *
     * @var string|null
     */
    public $occurred_at;

    /**
     * A given incident template.
     *
     * @var string|null
     */
    public $template;

    /**
     * Variables for the incident template.
     *
     * @var string[]|null
     */
    public $template_vars;

    /**
     * Meta key/value pairs.
     *
     * @var array
     */
    public $meta = [];

    /**
     * The validation rules.
     *
     * @var string[]
     */
    public $rules = [
        'name'              => 'nullable|string',
        'status'            => 'nullable|int|min:0|max:4',
        'message'           => 'nullable|string',
        'visible'           => 'nullable|bool',
        'components'        => 'nullable|required_with:component_status|array',
        'component_status'  => 'nullable|required_with:components|int|min:0|max:4',
        'stickied'          => 'nullable|bool',
        'occurred_at'       => 'nullable|string',
        'template'          => 'nullable|string',
        'meta'              => 'nullable|array',
    ];

    /**
     * Create a new update incident command instance.
     *
     * @param \CachetHQ\Cachet\Models\Incident $incident
     * @param string                           $name
     * @param int                              $status
     * @param string                           $message
     * @param int                              $visible
     * @param array                            $components
     * @param int                              $component_status
     * @param bool                             $notify
     * @param bool                             $notify_nh_clients
     * @param bool                             $stickied
     * @param string|null                      $occurred_at
     * @param string|null                      $template
     * @param array                            $template_vars
     * @param array                            $meta
     *
     * @return void
     */
    public function __construct(Incident $incident, $name, $status, $message, $visible, $components, $component_status, $stickied, $occurred_at, $template, array $template_vars = [], array $meta = [])
    {
        $this->incident = $incident;
        $this->name = $name;
        $this->status = $status;
        $this->message = $message;
        $this->visible = $visible;
        $this->components = $components;
        $this->component_status = $component_status;
        $this->stickied = $stickied;
        $this->occurred_at = $occurred_at;
        $this->template = $template;
        $this->template_vars = $template_vars;
        $this->meta = $meta;
    }
}
