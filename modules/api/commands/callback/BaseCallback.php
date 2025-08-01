<?php

namespace app\modules\api\commands\callback;

use yii\base\BaseObject;

class BaseCallback extends BaseObject
{
    private $query;
    public function __construct($config = [])
    {
        session_id(\Yii::$app->telegram->input->callback_query->from["id"]);
        $this->query = \Yii::$app->telegram->input->callback_query;
        parent::__construct($config);
    }
}