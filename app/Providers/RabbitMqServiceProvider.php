<?php

namespace CachetHQ\Cachet\Providers;

use CachetHQ\Cachet\Notifications\Channels\RabbitMqChannel;
use Illuminate\Container\Container;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\ServiceProvider;
use PhpAmqpLib\Connection\AMQPStreamConnection;

class RabbitMqServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot(): void
    {
        $this->app->when(RabbitMqChannel::class)
            ->needs(AMQPStreamConnection::class)
            ->give(static function () {
                return new AMQPStreamConnection(
                    env('RABBITMQ_HOST'),
                    env('RABBITMQ_PORT'),
                    env('RABBITMQ_USER'),
                    env('RABBITMQ_PASS')
                );
            });
    }

    /**
     * Register any package services.
     */
    public function register(): void
    {
        Notification::extend('rabbitmq', static function (Container $app) {
            return $app->make(RabbitMqChannel::class);
        });
    }
}
