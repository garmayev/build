<?php

namespace app\modules\notifications\handlers;

class HandlerFactory
{
    public static function create($action)
    {
        switch ($action) {
            case 'start':
                return new StartHandler();
            case 'accept':
                return new AcceptHandler();
            case 'decline':
                return new DeclineHandler();
            default:
                return new DefaultHandler();
        }
    }
}