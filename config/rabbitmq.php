<?php
/**
 * User: xiangzhiping
 * Date: 2018/7/5
 */

define('MQ_CHANNEL_DEFAULT', 1);


return [
    'default' => [
        'base'     => [
            'host'  => '192.168.10.10',
            'port'  => '5672',
            'user'  => 'guest',
            'pwd'   => 'guest',
            'vhost' => '/',
            'persistent' => true, // 是否持久链接
        ],
        'channels' => [// 数组的键必须是数字  1 ~ 65535
            MQ_CHANNEL_DEFAULT=>[
                'queue'         => [
                    'name'        => 'test',  // 队列名称
                    'durable'     => true, // 队列持久化  服务重启 存活
                    'auto_delete' => false, // 通道关闭 是否 自动删除队列   结合 exclusive 使用，可以建立一个临时的 queue。
                    'routing_key' => 'test',
                    'exclusive'   => false, // 如果设置成 true，那么只有定义 queue 的应用可以使用
                    'passive'     => false, // 设置成 true 当 queue 存在的时候就会返回成功，而 queue 不存在的时候也没有去创建，只是返回失败
                ],
                'exchange'      => [
                    'name'        => 'amq.direct', // 交换机名称
                    'type'        => 'direct', // 交换机类型：
                    'durable'     => true, // 交换机持久化
                    'auto_delete' => false, // 通道关闭 是否 自动删除交换机
                ],
                'delivery_mode' => 2,   // 存入消息需设置的
                'format'        => 'json',
                'write'         => true,
            ],
        ],
    ],
];