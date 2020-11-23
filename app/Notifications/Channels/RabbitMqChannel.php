<?php

namespace CachetHQ\Cachet\Notifications\Channels;

use Exception;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class RabbitMqChannel
{
    private $connection;

    public function __construct(AMQPStreamConnection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * Send the given notification.
     *
     * @param mixed $notifiable
     * @param \Illuminate\Notifications\Notification $notification
     *
     * @return bool
     */
    public function send($notifiable, Notification $notification)
    {
        $message = $notification->toRabbitMq($notifiable);

        try {
            $channel = $this->connection->channel();

            $queueName = env('STATUS_PAGE_TASK_QUEUE');

            $channel->queue_declare($queueName, false, true, false, false);

            $task = new AMQPMessage(
                $message->toJson(),
                ['delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT]
            );

            $channel->basic_publish($task, '', $queueName);

            Log::info('Sent message to rabbitmq with payload:' . $message->toJson());

            $channel->close();

            $this->connection->close();

            return true;
        } catch (Exception $exception) {
            Log::error($exception->getMessage());
        }
    }
}
