<?php
/**
 * 参考 bschmitt/laravel-amqp 扩展
 */
namespace Support\Engine;

use Closure;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Exception\AMQPTimeoutException;
use PhpAmqpLib\Message\AMQPMessage;
use Support\Exception\RabbitMQStop as Stop;

class RabbitMQ
{
    private $connection;

    private $channel;

    private $routing;

    private $queueInfo;

    private function __construct(array $propertise)
    {
        if ($this->connection === null || $this->channel === null) {
            $this->connection = new AMQPStreamConnection(
                $propertise['host'],
                $propertise['port'],
                $propertise['user'],
                $propertise['password'],
                $propertise['vhost'],
            );
            $this->channel = $this->connection->channel();
        }
    }

    public static function publish($routing, $message, array $propertise = [])
    {
        $config = $_ENV['rabbitmq'];
        $config = array_merge($config, $propertise);
        $rabbitMq = new self($config);
        $rabbitMq->routing = $routing;
        $rabbitMq->initialization($config);

        if (!$message instanceof AMQPMessage) {
            $message = new AMQPMessage($message, [
                'content_type' => 'text/plain',
                'delivery_mode' => 2,
            ]);
        }

        $rabbitMq->channel->basic_publish($message, $config['exchange'], $routing);

        $rabbitMq->channel->close();
        $rabbitMq->connection->close();

        return true;
    }

    public static function consume($queue, Closure $callback, array $propertise = [])
    {
        $config = $_ENV['rabbitmq'];
        $config = array_merge($config, $propertise);
        $config['queue'] = $queue;
        $rabbitMq = new self($config);
        $rabbitMq->routing = null;
        $rabbitMq->initialization($config);

        try {
            $messageCount = 0;
            if (isset($rabbitMq->queueInfo[1])) {
                $messageCount = $rabbitMq->queueInfo[1];
            }
            if ((!isset($config['consumer_persistent']) || $config['persistent'] == false) &&
                $messageCount == 0
            ) {
                throw new Stop();
            }

            if (isset($config['qos']) && $config['qos'] == true) {
                $rabbitMq->channel->basic_qos(
                    isset($config['qos_prefetch_size']) ? $config['qos_prefetch_size'] : 0,
                    isset($config['qos_prefetch_count']) ? $config['qos_prefetch_count'] : 1,
                    isset($config['qos_a_global']) && $config['qos_a_global'] === true ? true : false
                );
            }

            $rabbitMq->channel->basic_consume(
                $queue,
                isset($config['consumer_tag']) && !empty($config['consumer_tag']) ? $config['consumer_tag'] : '',
                isset($config['consumer_no_local']) && $config['consumer_no_local'] === true ? ture : false,
                isset($config['consumer_no_ack']) && $config['consumer_no_ack'] === true ? ture : false,
                isset($config['consumer_exclusive']) && $config['consumer_exclusive'] === true ? ture : false,
                isset($config['consumer_nowait']) && $config['consumer_nowait'] === true ? ture : false,
                function ($message) use ($callback, $rabbitMq) {
                    $callback($message, $rabbitMq);
                },
                isset($config['consumer_ticket']) ? $config['consumer_ticket'] : null,
                isset($config['consumer_arguments']) && !empty($config['consumer_arguments']) ? $config['consumer_arguments'] : []
            );

            while (count($rabbitMq->channel->callbacks)) {
                $rabbitMq->channel->wait(
                    null,
                    false,
                    isset($config['consumer_timeout']) ? $config['consumer_timeout'] : 0,
                );
            }
        } catch (Stop $e) {
            return true;
        } catch (AMQPTimeoutException $e) {
            return true;
        }

        $rabbitMq->channel->close();
        $rabbitMq->connection->close();

        return true;
    }

    public function acknowledge(AMQPMessage $message)
    {
        $message->delivery_info['channel']->basic_ack(
            $message->delivery_info['delivery_tag']
        );

        if ($message->body === 'quit') {
            $message->delivery_info['channel']->basic_cancel(
                $message->delivery_info['consumer_tag']
            );
        }
    }

    public function reject(AMQPMessage $message, bool $requeue = false)
    {
        $message->delivery_info['channel']->basic_reject(
            $message->delivery_info['delivery_tag'],
            $requeue
        );
    }

    private function initialization(array $config)
    {
        $this->channel->exchange_declare(
            $config['exchange'],
            $config['exchange_type'],
            isset($config['exchange_passive']) && $config['exchange_passive'] === true ? ture : false,
            isset($config['exchange_durable']) && $config['exchange_durable'] === true ? ture : false,
            isset($config['exchange_auto_delete']) && $config['exchange_auto_delete'] === false ? false : ture,
            isset($config['exchange_internal']) && $config['exchange_internal'] === true ? ture : false,
            isset($config['exchange_nowait']) && $config['exchange_nowait'] === true ? ture : false,
            isset($config['exchange_arguments']) && !empty($config['exchange_arguments']) ? $config['exchange_arguments'] : [],
            isset($config['exchange_ticket']) ? $config['exchange_ticket'] : null,
        );

        $this->queueInfo = $this->channel->queue_declare(
            $config['queue'],
            isset($config['queue_passive']) && $config['queue_passive'] === true ? ture : false,
            isset($config['queue_durable']) && $config['queue_durable'] === true ? ture : false,
            isset($config['queue_exclusive']) && $config['queue_exclusive'] === true ? ture : false,
            isset($config['queue_auto_delete']) && $config['queue_auto_delete'] === false ? false : ture,
            isset($config['queue_nowait']) && $config['queue_nowait'] === true ? ture : false,
            isset($config['queue_arguments']) && !empty($config['queue_arguments']) ? $config['queue_arguments'] : [],
            isset($config['queue_ticket']) ? $config['queue_ticket'] : null,
        );

        foreach ((array) $this->routing as $routingKey) {
            $this->channel->queue_bind(
                $config['queue'] ?: $this->queueInfo[0],
                $config['exchange'],
                $routingKey
            );
        }
        $this->connection->set_close_on_destruct(true);
        return true;
    }
}
