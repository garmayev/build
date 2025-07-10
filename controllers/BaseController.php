<?php

namespace app\controllers;

use app\components\behaviors\UserStatusBehavior;
use yii\web\Controller;

class BaseController extends Controller
{
    public function behaviors()
    {
        return [
            UserStatusBehavior::class,
        ];
    }
}