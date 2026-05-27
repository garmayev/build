<?php

namespace app\models\forms;

class SubscriptionForm extends \yii\base\Model
{
    public $url;
    public $update_types;

    const TYPE_BOT_ADDED = 'bot_added';
    const TYPE_BOT_STARTED = 'bot_started';
    const TYPE_BOT_STOPPED = 'bot_stopped';
    const TYPE_BOT_REMOVED = 'bot_removed';
    const TYPE_CHAT_TITLE_CHANGED = 'chat_title_changed';
    const TYPE_DIALOG_CLEARED = 'dialog_cleared';
    const TYPE_DIALOG_MUTED = 'dialog_muted';
    const TYPE_DIALOG_UNMUTED = 'dialog_unmuted';
    const TYPE_DIALOG_REMOVED = 'dialog_removed';
    const TYPE_MESSAGE_CALLBACK = 'message_callback';
    const TYPE_MESSAGE_CREATED = 'message_created';
    const TYPE_MESSAGE_EDITED = 'message_edited';
    const TYPE_MESSAGE_REMOVED = 'message_removed';
    const TYPE_USER_ADDED = 'user_added';
    const TYPE_USER_REMOVED = 'user_removed';

    public function rules()
    {
        return [
            [['url', 'types'], 'required'],
            [['url'], 'string'],
            [['update_types'], 'each', 'rule' => ['in', 'range' => self::getTypeLabels()]],
        ];
    }

    public function getAttributeLabels()
    {
        return [
            'url' => \Yii::t('app', 'Url'),
            'update_types' => \Yii::t('app', 'Update Types')
        ];
    }

    public function getTypeLabels()
    {
        return [
            self::TYPE_BOT_ADDED =>		\Yii::t('app', self::TYPE_BOT_ADDED),
            self::TYPE_BOT_STARTED => 		\Yii::t('app', self::TYPE_BOT_STARTED),
            self::TYPE_BOT_STOPPED => 		\Yii::t('app', self::TYPE_BOT_STOPPED),
            self::TYPE_BOT_REMOVED => 		\Yii::t('app', self::TYPE_BOT_REMOVED),
            self::TYPE_CHAT_TITLE_CHANGED => 	\Yii::t('app', self::TYPE_CHAT_TITLE_CHANGED),
            self::TYPE_DIALOG_CLEARED => 	\Yii::t('app', self::TYPE_DIALOG_CLEARED),
            self::TYPE_DIALOG_MUTED => 		\Yii::t('app', self::TYPE_DIALOG_MUTED),
            self::TYPE_DIALOG_UNMUTED => 	\Yii::t('app', self::TYPE_DIALOG_UNMUTED),
            self::TYPE_DIALOG_REMOVED => 	\Yii::t('app', self::TYPE_DIALOG_REMOVED),
            self::TYPE_MESSAGE_CALLBACK => 	\Yii::t('app', self::TYPE_MESSAGE_CALLBACK),
            self::TYPE_MESSAGE_CREATED => 	\Yii::t('app', self::TYPE_MESSAGE_CREATED),
            self::TYPE_MESSAGE_EDITED => 	\Yii::t('app', self::TYPE_MESSAGE_EDITED),
            self::TYPE_MESSAGE_REMOVED => 	\Yii::t('app', self::TYPE_MESSAGE_REMOVED),
            self::TYPE_USER_ADDED => 		\Yii::t('app', self::TYPE_USER_ADDED),
            self::TYPE_USER_REMOVED => 		\Yii::t('app', self::TYPE_USER_REMOVED),
        ];
    }

    public function save()
    {
        $data = \Yii::$app->max->setWebhook($this->url, $this->update_types);
        // \Yii::error($data);
        return $data;
    }

    public function delete()
    {
        $data = \Yii::$app->max->deleteWebhook($this->url);
        // \Yii::error($data);
        return $data;
    }
}