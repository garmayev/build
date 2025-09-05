<?php

namespace app\modules\api\commands\callback;

use app\modules\api\commands\callback\BaseCallback;
use app\modules\api\commands\CommandInterface;

class AttachReportCallback extends BaseCallback implements CommandInterface
{

    public function handle($telegram, $args)
    {
        $query = $telegram->input->callback_query;
        parse_str($args[0] ?? '', $data);
        $orderId = $data["order_id"] ?? null;
        $reportId = $data["report_id"] ?? null;
        \Yii::error($orderId);
        \Yii::error($reportId);
        if (isset($orderId) && isset($reportId)) {
            $order = \app\models\Order::findOne($orderId);
            $report = \app\models\Report::findOne($reportId);
            $order->link('reports', $report);
            $keyboard = [
                [['text' => \Yii::t('telegram', 'button_menu'), 'callback_data' => '/menu']]
            ];
            $telegram->editMessageText([
                'chat_id' => $query->from['id'],
                'text' => \Yii::t('telegram', 'message_report_attached'),
                'message_id' => $query->message['message_id'],
                'reply_markup' => json_encode(['inline_keyboard' => $keyboard])
            ]);
        }
    }
}