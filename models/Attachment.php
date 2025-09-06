<?php

namespace app\models;

use yii\db\ActiveRecord;
use yii\web\UploadedFile;

/**
 * @property string $url
 * @property string $target_class
 * @property int $target_id
 *
 * @property UploadedFile $file
 */
class Attachment extends ActiveRecord
{
    public $file;

    public static function tableName()
    {
        return "attachment";
    }

    public function rules()
    {
        return [
            [['url', 'target_class'], 'string'],
            [['target_id'], 'integer'],
            [['file'], 'file', 'skipOnEmpty' => false]
        ];
    }

    public function beforeDelete()
    {
        unlink(\Yii::getAlias("@webroot").$this->url);
        return parent::beforeDelete();
    }

    public function upload()
    {
        $name = md5($this->file->baseName);
        $filename = "$name.{$this->file->extension}";
        $this->url = "/upload/$filename";
        return $this->file->saveAs(\Yii::getAlias('@webroot')."/upload/$filename");
    }

    /**
     * @param $baseUrl boolean
     * @return string
     */
    public function getLink($baseUrl = false): string
    {
        return $baseUrl ? \yii\helpers\Html::a($this->url, \yii\helpers\Url::to([$this->url], true)) : $this->url;
    }
}
