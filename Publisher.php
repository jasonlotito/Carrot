<?php

namespace Carrot;

use PhpAmqpLib\Message\AMQPMessage;

/**
 * Class Publisher
 *
 * Simple publisher for RabbitMQ
 *
 * @package JLO
 */
class Publisher extends Carrot
{

  /**
   * @var bool
   */
  private $doBatchPublish = false;

  function __construct( $exchange, $config = [] )
  {
    $this->exchange = $exchange;

    parent::__construct($config);
  }

  /**
   * @param string $routingKey
   * @param mixed $message
   */
  public function publish($routingKey, $message)
  {
    $msg = $this->buildMessage($message);
    $channel = $this->getChannel();
    $channel->basic_publish($msg, $this->exchange, $routingKey);
  }

  /**
   * @param string $routingKey
   * @param mixed $message
   */
  public function eventuallyPublish($routingKey, $message)
  {
    $msg = $this->buildMessage($message);
    $channel = $this->getChannel();
    $channel->batch_basic_publish($msg, $this->exchange, $routingKey);
    $this->registerShutdownHandler();
  }

  public function finallyPublish()
  {
    if ($this->doBatchPublish) {
      $this->doBatchPublish = false;
      $this->getChannel()->publish_batch();
    }
  }

  /**
   * @param mixed $message
   * @return AMQPMessage
   */
  protected function buildMessage($message)
  {
    return new AMQPMessage(json_encode($message), ['content_type' => 'application/json']);
  }

  private function registerShutdownHandler()
  {
    static $registered;

    if (!$registered) {
      $registered = true;
      $this->doBatchPublish = true;
      register_shutdown_function([$this, 'finallyPublish']);
    }
  }
}
