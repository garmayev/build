<?php

namespace app\modules\api\commands\callback;

use app\modules\api\commands\callback\BaseCallback;
use app\modules\api\commands\CommandInterface;

class ReportStartCallback extends BaseCallback implements CommandInterface
{

    public function handle($telegram, $args)
    {
        $query = $telegram->input->callback_query;

        $chatId = $query->from->id;
        $report = new \app\models\Report();
        if ($report->save()) {
            \Yii::$app->session->set('report_id', $report->id);
            \Yii::error($report->id);
        } else {
            \Yii::error($report->errors);
            return ;
        }

        $telegram->editMessageText([
            'message_id' => $query->message_id,
            'chat_id' => $message->from->id,
            'text' => \Yii::t('telegram', 'message_start_report'),
        ]);
    }
}