<?php

namespace Carrot;

class Consumer extends Carrot
{
  protected $listeningTo = array();

  /**
   * @param $queueName
   * @param $exchangeName
   * @param $routingKey
   * @return $this
   */
  public function bind( $queueName, $exchangeName, $routingKey )
  {
    $this->getChannel()->queue_bind($queueName, $exchangeName, $routingKey);

    return $this;
  }

  /**
   * @param $queueName
   * @param $handler
   * @return $this
   */
  public function listenTo($queueName, $handler)
  {
    $channel = $this->getChannel();

    /** @var \PhpAmqpLib\Message\AMQPMessage $message */
    $callback = function($message) use ($handler, $channel, $queueName){
      echo "Processing: $queueName\n";
      try {
        $res = $handler($message->body, $message->get_properties());
      } catch ( \Exception $e ) {
        $res = false;
      }

      if( $res ) {
        $channel->basic_ack($message->delivery_info['delivery_tag']);
      }
    };

    $channel->basic_consume($queueName, "", false, false, false, false, $callback);

    $this->listeningTo[] = $queueName;

    return $this;
  }

  public function listenAndWait()
  {
    $channel = $this->getChannel();

    echo "Listening to: " , json_encode($this->listeningTo), PHP_EOL;

    while(count($channel->callbacks)) {
      $channel->wait();
    }
  }
}
