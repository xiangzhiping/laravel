<?php

namespace App\Component;

use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

/**
 * 创建人: xzp
 * 创建时间: 2017-03-07
 */
class RabbitMq
{

    // 基本配置：
    private $host;
    private $port;
    private $user;
    private $pwd;
    private $vhost;

    /**
     * @var callable
     */
    private $callback; // 消费回调

    private $checkReturn; // 是否需要检查回调

    /**
     * @var AMQPStreamConnection
     */
    private $mq;

    /**
     * Channel 数组
     *
     * @var array
     */
    private $channels = [];

    /**
     * @var Channel
     */
    private $current;

    private $currentId = 1;

    private $rabbitMqConfig;


    public function __construct($host = 'default')
    {
        $config = config('rabbitmq');
        if (!isset($config[$host])) {
            throw new RabbitMqException('no rabbitMq config ' . $host);
        }

        $this->rabbitMqConfig = $config[$host];
        // 基本配置
        $base_config = $this->rabbitMqConfig['base'];
        $this->host  = $base_config['host'];
        $this->port  = $base_config['port'];
        $this->user  = $base_config['user'];
        $this->pwd   = $base_config['pwd'];
        $this->vhost = $base_config['vhost'];

        $this->mqConnect();
    }

    /**
     * mq链接
     */
    private function mqConnect()
    {
        $this->mq = new AMQPStreamConnection($this->host, $this->port, $this->user, $this->pwd, $this->vhost);
    }

    /**
     * 获取队列数量
     *
     * @param string $queue_name
     * @param int    $channelId 1~65535  不要跟已有的channel弄混，不然覆盖了channel的设置
     *
     * @return mixed
     */
    public function count($queue_name, $channelId = 65535)
    {
        // 使用一个无关的通道来获取队列长度
        $channel       = $this->mq->channel($channelId);

        // see: AMQPChannel::queue_declare_ok
        /** @var array $declare_info  [queueName, message_count, consume_count]*/
        $declare_info  = $channel->queue_declare($queue_name, true);
        $message_count = $declare_info[1];

        return $message_count;
    }

    /**
     * 获取当前通道
     *
     * @return Channel
     */
    public function getCurrentChannel()
    {
        return $this->current;
    }


    /**
     * 切换通道
     *
     * @param $channelId
     *
     * @return $this
     * @throws RabbitMqException
     */
    public function useChannel($channelId)
    {
        if (!isset($this->channels[$channelId])) {
            if (!isset($this->rabbitMqConfig['channels'][$channelId])) {
                throw new RabbitMqException('the rabbitMq config channels has no key ' . $channelId);
            }
            $channel                    = $this->mq->channel($channelId);
            $channel                    = new Channel($channel, $this->rabbitMqConfig['channels'][$channelId]);
            $this->channels[$channelId] = $channel;
        }
        $this->current   = $this->channels[$channelId];
        $this->currentId = $channelId;

        return $this;
    }

    // 重新连接：
    public function reconnect()
    {
        if ($this->mq) {
            $this->mq->reconnect();
        } else {
            $this->mqConnect();
        }
        $this->useChannel($this->currentId);
    }

    /**
     * 检测通道是否准备好
     *
     * @throws RabbitMqException
     */
    public function check()
    {
        if ($this->mq && !$this->current) $this->useChannel($this->currentId);

        if (!$this->mq || !$this->mq->isConnected() || !$this->current) $this->reconnect();
    }


    /**
     * 确认一条消息消费
     *
     * @param $deliveryTag int 在channel中时递增的唯一的
     * @param $multi       bool 是否批量确认  true: <= $deliveryTag 都会确认消费  否则 只有 $deliveryTag 的消费
     */
    public function ack($deliveryTag, $multi = false)
    {
        $this->current->basic_ack($deliveryTag, $multi);
    }

    /**
     * 获取一条消息
     *
     * @param bool $no_ack 是否拿完就消费
     *
     * @return AMQPMessage
     *
     * @throws
     */
    public function get($no_ack = false)
    {
        $this->check();

        return $this->current->basic_get($no_ack);
    }

    /**
     * 发布消息
     *
     * @param $msg
     *
     * @throws
     */
    public function publish($msg)
    {
        $this->check();

        if (is_array($msg)) {
            $this->current->batch_publish($msg);
        } else {
            $this->current->basic_publish($msg);
        }
    }

    /**
     * 常驻消费处理
     *
     * @param string   $consumer_tag
     * @param callable $callback    回调处理消息
     * @param bool     $checkReturn 是否根据消费结果来判断 消息确认消费
     *
     * @throws
     */
    public function consume($consumer_tag = '', $callback, $checkReturn = false)
    {
        $this->check();

        $this->callback    = $callback;
        $this->checkReturn = $checkReturn;

        $this->current->basic_consume([$this, 'process_message'], $consumer_tag);
    }

    /**
     * 消息确认消费
     *
     * @param AMQPMessage $msgObj
     */
    public function ackMsg($msgObj)
    {
        $msgObj->delivery_info['channel']->basic_ack($msgObj->delivery_info['delivery_tag']);
    }

    /**
     *  处理消息：
     *
     * @param $msgObj AMQPMessage
     */
    public function process_message($msgObj)
    {
        $result = call_user_func($this->callback, $msgObj->body);
        if (!$this->checkReturn || $result) {
            $this->ackMsg($msgObj);
        }
    }

    // 关闭链接处理
    public function close()
    {
        try {
            $this->current = null;
            if ($this->mq) $this->mq->close();
        } catch (\Exception $e) {
            // ignore
        }
        $this->mq = null;
    }

    // 析构函数
    public function __destruct()
    {
        $this->close();
    }
}

class Channel
{

    /**
     * @var AMQPChannel
     */
    private $channel;


    // 队列配置：
    private $queue_name        = '';    // 队列名称
    private $queue_durable     = true;  // 队列持久化  服务重启 存活
    private $queue_auto_delete = false; // 通道关闭 是否 自动删除队列
    private $queue_exclusive   = false;
    private $queue_passive     = false;
    private $routing_key;

    // 交换机设置
    private $exchange_name        = '';    // 交换机名称
    private $exchange_type        = 'direct';  // 交换机类型：
    private $exchange_durable     = true;  // 交换机持久化
    private $exchange_auto_delete = false; // 通道关闭 是否 自动删除交换机
    private $exchange_passive     = false;

    // 消息设置：
    private $delivery_mode = AMQPMessage::DELIVERY_MODE_PERSISTENT;
    private $writeAble     = true;
    private $format        = 'json';


    /**
     * Channel constructor.
     *
     * @param AMQPChannel $channel
     * @param array       $channelConfig
     */
    public function __construct($channel, $channelConfig)
    {
        $this->channel = $channel;

        // 队列配置：
        $queue_config = $channelConfig['queue'];
        if (isset($queue_config['name'])) $this->queue_name = $queue_config['name'];
        if (isset($queue_config['durable'])) $this->queue_durable = $queue_config['durable'];
        if (isset($queue_config['auto_delete'])) $this->queue_auto_delete = $queue_config['auto_delete'];
        if (isset($queue_config['routing_key'])) $this->routing_key = $queue_config['routing_key'];
        if (isset($queue_config['exclusive'])) $this->queue_exclusive = $queue_config['exclusive'];
        if (isset($queue_config['passive'])) $this->queue_passive = $queue_config['passive'];

        // 交换机配置：
        $exchange_config = $channelConfig['exchange'];
        if (isset($exchange_config['name'])) $this->exchange_name = $exchange_config['name'];    // 交换机名称
        if (isset($exchange_config['type'])) $this->exchange_type = $exchange_config['type'];  // 交换机类型
        if (isset($exchange_config['durable'])) $this->exchange_durable = boolval($exchange_config['durable']);
        if (isset($exchange_config['auto_delete'])) $this->exchange_auto_delete = $exchange_config['auto_delete'];
        if (isset($exchange_config['passive'])) $this->exchange_passive = $exchange_config['passive'];

        if (isset($channelConfig['delivery_mode'])) $this->delivery_mode = $channelConfig['delivery_mode'];
        if (isset($channelConfig['format'])) $this->format = $channelConfig['format'];
        if (isset($channelConfig['write'])) $this->writeAble = $channelConfig['write'];

        $this->setChannel();
    }

    /**
     * 设置channel
     */
    private function setChannel()
    {
        // 是否有写权限：
        $this->channel->queue_declare($this->queue_name, $this->queue_passive, $this->queue_durable, $this->queue_exclusive, $this->queue_auto_delete);
        if ($this->writeAble) { // 可写权限进行绑定
            $this->channel->exchange_declare($this->exchange_name, $this->exchange_type, $this->exchange_passive, $this->exchange_durable, $this->exchange_auto_delete);
            $this->channel->queue_bind($this->queue_name, $this->exchange_name, $this->routing_key);
        }
    }

    /**
     * 发布一条消息
     *
     * @param string $msg
     *
     * @return void
     */
    public function basic_publish($msg)
    {
        $msgObj = $this->getMsgObj($msg);
        $this->channel->basic_publish($msgObj, $this->exchange_name, $this->routing_key);
    }

    /**
     * 批量发布消息
     *
     * @param array $msgList
     *
     * @return void
     */
    public function batch_publish(array $msgList)
    {
        foreach ($msgList as $msg) {
            $msgObj = $this->getMsgObj($msg);
            $this->channel->batch_basic_publish($msgObj, $this->exchange_name, $this->routing_key);
        }
        $this->channel->publish_batch();
    }

    /**
     * 创建消息对象
     *
     * @param $msg
     *
     * @return AMQPMessage
     */
    public function getMsgObj($msg)
    {
        return new AMQPMessage($msg, ['content_type' => $this->format, 'delivery_mode' => $this->delivery_mode]);
    }


    /**
     * 获取一条消息 并确认消费
     *
     * @param bool $no_ack
     *
     * @return mixed
     */
    public function basic_get($no_ack = false)
    {
        return $this->channel->basic_get($this->queue_name, $no_ack);
    }

    /**
     * 确认一条消息消费
     *
     * @param $deliveryTag int 在channel中时递增的唯一的
     * @param $multi       bool 是否批量确认  true: <= $deliveryTag 都会确认消费  否则 只有 $deliveryTag 的消费
     */
    public function basic_ack($deliveryTag, $multi = false)
    {
        $deliveryTag && $this->channel->basic_ack($deliveryTag, $multi);
    }

    /**
     * 限制通道消费处理能力（流量控制）
     *
     * @param int $count
     */
    public function basic_qos(int $count)
    {
        $count > 0 && $this->channel->basic_qos(null, $count, null);
    }

    /**
     * 常驻消费
     *
     * @param        $callback
     * @param string $consumer_tag
     */
    public function basic_consume($callback, $consumer_tag = '')
    {
        $this->channel->basic_consume($this->queue_name, $consumer_tag, false, false, false, false, $callback);
        while (count($this->channel->callbacks)) {
            $this->channel->wait();
        }
    }

    /**
     * 获取队列名称
     *
     * @return string
     */
    public function getQueueName()
    {
        return $this->queue_name;
    }


    /**
     * 关闭
     */
    public function close()
    {
        try {
            if ($this->channel) $this->channel->close();
        } catch (\Exception $e) {
            // ignore
        }
        $this->channel = null;
    }

    public function __destruct()
    {
        $this->close();
    }
}

class RabbitMqException extends \ErrorException
{
}

