<?php

namespace app\modules\notifications\services;

interface Service
{
    public function send($notification);
    public function handleCallback($data);
}