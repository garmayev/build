<?php

namespace app\components\behaviors;

use app\models\Logger;
use Yii;
use yii\base\Behavior;
use yii\base\Event;
use yii\db\ActiveRecord;
use yii\db\BaseActiveRecord;

class LogBehavior extends Behavior
{
    public $ignoreAttributes = [];
    public function init()
    {
        parent::init();
    }

    public function events()
    {
        return [
//            BaseActiveRecord::EVENT_AFTER_INSERT => 'handleLog',
            BaseActiveRecord::EVENT_AFTER_UPDATE => 'handleLog',
            BaseActiveRecord::EVENT_AFTER_DELETE => 'handleLog',
        ];
    }

    public function handleLog(Event $event)
    {
        /** @var $model ActiveRecord */
        $model = $event->sender;
        $oldValues = [];
        $newValues = [];
        $targetAttributes = [];
        if (property_exists($event, 'changedAttributes') || method_exists($event, 'changedAttributes')) {
            $attributes = array_merge($model->oldAttributes, $event->changedAttributes);
            foreach ($event->changedAttributes as $key => $attribute) {
                $newValues[$key] = $model->getAttribute($attribute);
                $targetAttributes[] = $key;
            }
            foreach ($attributes as $attribute) {
                $oldValues[$attribute] = $model->getAttribute($attribute);
            }
        }
        Logger::create(
            $model::className(),
            $this->getPK($model),
            $targetAttributes,
            $event->name,
            $event->changedAttributes ?? [],
            $model->attributes,
        );
    }

    /**
     * Returns the PK of a given model given that it's formed by only one field.
     * Rows with multiple fields as PKs are not currently supported
     * @param $model
     * @return false|mixed
     */
    private function getPK($model)
    {
        $pks = $model::getTableSchema()->primaryKey;

        if (count($pks) === 1)
            return $model->{$pks[0]};

        return null;
    }
}