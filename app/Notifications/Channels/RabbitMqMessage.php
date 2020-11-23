<?php

namespace CachetHQ\Cachet\Notifications\Channels;

class RabbitMqMessage
{
    public  $payload;

    public static function create(string $message = null): self
    {
        return new static($message);
    }

    public function __construct(?string $message = null)
    {
        if (null !== $message) {
            $this->payload['message'] = $message;
        }
    }

    public function withServers($components): self
    {
        $list = [];
        $status = null;
        foreach ($components as $component) {
            $list[] = $component['name'];
            $status ?? $status = trans('cachet.components.status')[$component['status']];
        }

        $this->payload['server'] = [
            'list'   => $list,
            'status' => $status
        ];

        return $this;
    }

    public function asNewIncident(): self
    {
        $this->payload['type'] = 'new_incident';

        return $this;
    }

    public function asIncidentUpdate(): self
    {
        $this->payload['type'] = 'incident_update';

        return $this;
    }

    public function asMaintenance(): self
    {
        $this->payload['type'] = 'maintenance';

        return $this;
    }

    public function withMaintenanceStatus(string $status): self
    {
        $this->payload['maintenance_status'] = $status;

        return $this;
    }

    public function withIncidentStatus(string $status): self
    {
        $this->payload['incident_status'] = $status;

        return $this;
    }

    public function withSubject(string $subject): self
    {
        $this->payload['subject'] = $subject;

        return $this;
    }

    public function toJson(): string
    {
        return json_encode($this->payload);
    }
}
