<?php

namespace app\modules\api\commands\callback;

use app\modules\api\commands\callback\BaseCallback;
use app\modules\api\commands\CommandInterface;

class OrderStatusProcessCallback extends BaseCallback implements CommandInterface
{

    public function handle($telegram, $args)
    {
        $query = $telegram->input->callback_query;
        parse_str($args[0] ?? '', $data);
        $id = $data["order_id"] ?? null;
        if (empty($id)) {
            return ;
        }
        $order = \app\models\Order::findOne($id);
        $order->status = \app\models\Order::STATUS_PROCESS;
        if ($order->save()) {
            $order
        }
    }
}