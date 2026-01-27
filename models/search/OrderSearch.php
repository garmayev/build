<?php

namespace app\models\search;

use app\models\Building;
use app\models\Coworker;
use app\models\Order;
use Yii;
use yii\data\ActiveDataFilter;
use yii\data\ActiveDataProvider;

class OrderSearch extends Order
{
    public $title;
    public $building_id;
    public $mode;
    public $start_datetime;

    public function rules(): array
    {
        return [
            [['title'], 'string'],
            [['building_id'], 'exist', 'targetClass' => Building::class, 'targetAttribute' => ['building_id' => 'id']],
            [['mode'], 'in', 'range' => [Order::MODE_SINGLE_FIXED, Order::MODE_LONG_FIXED, Order::MODE_LONG_DAILY]],
        ];
    }

    public function search($params)
    {
        if (\Yii::$app->user->can('admin')) {
            $query = Order::find();
        } else {
            $query = Order::find()->where(['created_by' => \Yii::$app->user->identity->getId()]);
        }

//        \Yii::error($params);
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 20,
            ],
            'sort' => [
                'defaultOrder' => [
                    'id' => SORT_DESC,
                ]
            ]
        ]);
        if (!$this->load($params)) {
            return $dataProvider;
        }

        if (!empty($this->title)) {
            $query->andWhere(['like', 'title', $this->title]);
        }
        if (!empty($this->building_id)) {
            $query->andWhere(['building_id' => $this->building_id]);
        }
        if (in_array($this->mode, [0, 1, 2])) {
            $query->andWhere(['mode' => $this->mode]);
        }

        \Yii::error($query->createCommand()->rawSql);
        return $dataProvider;
    }
}