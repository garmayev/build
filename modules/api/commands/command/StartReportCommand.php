<?php

namespace app\modules\api\commands\command;

use app\modules\api\commands\CommandInterface;

class StartReportCommand extends BaseCommand implements CommandInterface
{

    public function handle($telegram, $args)
    {
        $message = $telegram->input->message;

        $chatId = $message->from->id;
        $report = new \app\models\Report();
        if ($report->save()) {
            \Yii::$app->session->set('report_id', $report->id);
            \Yii::error($report->id);
        } else {
            \Yii::error($report->errors);
            return ;
        }

        $telegram->sendMessage([
            'chat_id' => $message->from->id,
            'text' => \Yii::t('telegram', 'message_start_report'),
        ]);
    }
}