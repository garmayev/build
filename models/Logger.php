<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * @property string $target_class
 * @property integer $target_id
 * @property string $target_attribute
 * @property string $action
 * @property string $new_value
 * @property string $old_value
 */
class Logger extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%logger}}';
    }

    public function rules()
    {
        return [
            [['target_id', 'target_class', 'action'], 'required'],
            [['target_class', 'target_attribute', 'action', 'new_value', 'old_value'], 'string'],
            [['target_id'], 'integer'],
        ];
    }

    public static function create($target_class, $target_id, $target_attribute, $action, $old_value, $new_value)
    {
        $model = new self();
        \Yii::debug($new_value);
        if ($model->load(['Logger' => [
            'target_id' => $target_id,
            'target_class' => $target_class,
            'target_attribute' => $target_attribute ? json_encode($target_attribute) : "",
            'action' => $action,
            'old_value' => count($old_value) ? json_encode($old_value) : "",
            'new_value' => count($new_value) ? json_encode($new_value) : ""
        ]]) && $model->save()) {
            \Yii::debug("Logger is saved");
        } else {
            \Yii::error($model->errors);
        }
    }
}