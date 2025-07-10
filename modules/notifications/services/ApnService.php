<?php

namespace app\modules\notifications\services;

use yii\base\Exception;

class ApnService implements Service
{
    private $config;
    private $client;

    public function __construct($config)
    {
        $this->config = $config;
    }

    public function send($notification)
    {
        $certificate = \Yii::getAlias($this->config['certificate']);

        if (!$certificate) {
            throw new Exception();
        }

        $ctx = stream_context_create();
        stream_context_set_option($ctx, 'ssl', 'local_cert', $certificate);
        stream_context_set_option($ctx, 'ssl', 'passphrase', $this->config['passphrase']);

        $server = $this->config['environment'] === 'production' ?
            'ssl://gateway.push.apple.com:2195' :
            'ssl://gateway.sandbox.push.apple.com:2195';

        $fp = stream_socket_client($server, $err, $err_str, 60, STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT, $ctx);
        if (!$fp) {
            throw new Exception("Failed to connect: $err $err_str");
        }

        $actions = [];
        foreach ($notification['actions'] as $action) {
            $actions[$action['name']] = $action['title'];
        }

        $body = [
            'aps' => [
                'alert' => [
                    'title' => $notification->title,
                    'body' => $notification->message,
                ],
                'sound' => 'default',
                'badge' => 1,
                'category' => 'CUSTOM_CATEGORY',
                'mutable-content' => true,
            ],
            'notification_id' => $notification->id,
            'actions' => $actions,
        ];

        $payload = json_encode($body);
        $msg = chr(0) . pack('n', 32) . pack('H*', $notification->recipient) . pack('n', strlen($payload)) . $payload;
        $result = fwrite($fp, $msg, strlen($msg));
        fclose($fp);

        return [
            'success' => $result !== false,
            'result' => $result
        ];
    }

    public function handleCallback($data)
    {
        return true;
    }
}