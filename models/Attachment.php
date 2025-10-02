<?php

namespace app\models;

use yii\bootstrap5\Html;
use yii\db\ActiveRecord;
use yii\helpers\Url;

/**
 * @property string $url
 * @property string $target_class
 * @property int $target_id
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
            [['file'], 'file', 'skipOnEmpty' => true]
        ];
    }

    public function beforeDelete()
    {
        unlink(\Yii::getAlias('@webroot') . $this->url);
        return parent::beforeDelete();
    }

    public function upload()
    {
        $name = md5($this->file->baseName."-".time());
        $filename = "{$name}.{$this->file->extension}";
        $this->url = "/upload/$filename";
        return $this->file->saveAs(\Yii::getAlias('@webroot')."/upload/$filename");
    }

    /**
     * @param $baseUrl boolean
     * @return string
     */
    public function getLink($baseUrl = false): string
    {
        return $baseUrl ? Html::a(Html::img($this->url, ['class' => 'glide__slide']), Url::to([$this->url], true), ['data-lg-size' => '1600-2400']) : Html::a(Html::img($this->url, ['class' => 'glide__slide']), [$this->url], ['data-lg-size' => '1600-2400', 'class' => 'image-container']);
    }
}
