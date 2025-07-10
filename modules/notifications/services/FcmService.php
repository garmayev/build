<?php

namespace app\modules\notifications\services;

class FcmService implements Service
{
    private $config;
    private $httpClient;

    public function __construct($config)
    {
        $this->config = $config;
        $this->httpClient = new \GuzzleHttp\Client();
    }

    public function send($notification)
    {
        $url = 'https://fcm.googleapis.com/fcm/send';

        $actions = [];
        foreach ($notification['actions'] as $action) {
            $actions[] = [
                'name' => $action['name'],
                'title' => $action['title'],
            ];
        }
        $data = [
            'notification_id' => $notification->id,
            'actions' => $actions,
        ];

        $payload = [
            'to' => $notification->recipient,
            'notification' => [
                'title' => $notification->title,
                'body' => $notification->message,
                'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
            ],
            'data' => $data,
        ];

        $response = $this->httpClient->post($url, json_encode($payload), [
            'Content-Type' => 'application/json',
            'Authorization' => 'key=' . $this->config['apiKey']
        ])->send();

        return [
            'success' => $response->isOk,
            'data' => $response->data,
        ];
    }

    public function handleCallback($data)
    {
        if (isset($data['action'])) {
            return true;
        }
        return false;
    }
}