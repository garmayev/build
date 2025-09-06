<?php

namespace app\modules\api\commands;

use app\modules\api\commands\command\BaseCommand;
use app\modules\api\commands\CommandInterface;

class PhotoHandler extends BaseCommand implements CommandInterface
{
    public function handle($telegram, $args)
    {
        $message = $telegram->input->message;

        $botId = $telegram->botToken;
        $file_url = [];
        $report_id = \Yii::$app->session->get('report_id');
        $report = \app\models\Report::findOne($report_id);

        \Yii::error($report_id);

        if (empty($report)) { return ; }

        $file_url[] = $this->downloadFile($message->photo[count($message->photo) - 1], $telegram);
        \Yii::error($file_url);
        $user = \app\models\User::findByChatId($message->from->id);
        if ($report && $report->load(['Report' => ['comment' => \Yii::$app->formatter->asDate(time()), 'created_at' => $message->date]]) && $report->save()) {
            \Yii::error('report saved');
            $report->setUrl($file_url);
        } else {
            \Yii::error($report->errors);
        }
    }

    private function downloadFile($file, $telegram): string
    {
        try {
            $fileDetail = $telegram->getFile(['file_id' => $file['file_id']]);

            if (!$fileDetail['ok']) {
                throw new \Exception("Не удалось получить информацию о файле");
            }

            $fileUrl = "https://api.telegram.org/file/bot{$telegram->botToken}/{$fileDetail['result']['file_path']}";
            $fileName = $fileDetail['result']['file_path'];
            $fileName = uniqid()."_".basename($fileName);
            $saveDir = \Yii::getAlias("@webroot");
            $savePath = "$saveDir/upload/{$fileName}";

            $fileContent = file_get_contents($fileUrl);
            if ($fileContent === false) {
                throw new \Exception("Не удалось скачать файл");
            }

            if (file_put_contents($savePath, $fileContent) === false) {
                throw new \Exception("Не удалось сохранить файл");
            } else {
                \Yii::error("Сохранено");
            }

            return "/upload/".$fileName;
        } catch (\Exception $e) {
            \Yii::error($e->getMessage());
        }
        return "";
    }
}