<?php

namespace Carrot;

use PhpAmqpLib\Connection\AMQPConnection;
use PhpAmqpLib\Exception\AMQPRuntimeException;

/**
 * Class Carrot
 *
 * @package Carrot
 */
abstract class Carrot
{
  /**
   * @var string
   */
  protected $host;
  /**
   * @var int
   */
  protected $port;
  /**
   * @var string
   */
  protected $user;
  /**
   * @var string
   */
  protected $pass;
  /**
   * @var string
   */
  protected $channelName = null;
  /**
   * @var string
   */
  protected $vhost;
  /**
   * @var string
   */
  protected $exchange;

  /**
   * @param array $config
   */
  public function __construct($config = [])
  {
    $this->host =  isset($config['host']) ? $config['host'] : 'localhost';
    $this->channelName =  isset($config['channelName']) ? $config['channelName'] : null;
    $this->vhost =  isset($config['vhost']) ? $config['vhost'] : '/';
    $this->port = isset($config['user']) ? $config['user'] : 5672;
    $this->user = isset($config['user']) ? $config['user'] : 'guest';
    $this->pass = isset($config['pass']) ? $config['pass'] : 'guest';
  }

  /**
   * @return \PhpAmqpLib\Channel\AMQPChannel
   */
  protected function getChannel()
  {
    static $channel;

    if (!isset($channel)) {
      $channel = $this->getAMQPConnection()->channel($this->channelName);
    }

    return $channel;
  }

  /**
   * @return AMQPConnection
   * @throws AMQPRuntimeException
   */
  protected function getAMQPConnection()
  {
    static $conn;

    if (!isset($conn)) {
      $conn = new AMQPConnection($this->host, $this->port, $this->user, $this->pass, $this->vhost);
    }

    return $conn;
  }
}
