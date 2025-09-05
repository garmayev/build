<?php

namespace app\modules\api\commands\command;

use app\modules\api\commands\Command;
use app\modules\api\commands\CommandInterface;

class StopReportCommand extends Command implements CommandInterface
{

    public function handle($telegram, $args)
    {
        $message = $telegram->input->message;

        $user = \app\models\User::findByChatId($message->from->id);
        $report_id = \Yii::$app->session->get('report_id');
        $keyboard = [];
        \Yii::error($report_id);
        foreach ($user->orders as $order) {
                $keyboard[] = [['text' => \Yii::t('app', 'Order #{id}', ['id' => $order->id]), 'callback_data' => '/attach_report_to_order order_id=' . $order->id . '&report_id=' . $report_id]];
        }
        \Yii::$app->session->remove('report_id');
        
        $telegram->sendMessage([
            'chat_id' => $message->chat->id,
            'text' => \Yii::t('telegram', 'message_report_saved'),
            'reply_markup' => json_encode([
                'inline_keyboard' => $keyboard
            ])
        ]);
    }
}