<?php

namespace app\models\search;

use app\models\Coworker;
use app\models\Order;
use Yii;
use yii\data\ActiveDataFilter;
use yii\data\ActiveDataProvider;

class OrderSearch extends Order
{
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
                'pageSize' => 10,
            ],
            'sort' => [
                'defaultOrder' => [
                    'id' => SORT_DESC,
                ]
            ]
        ]);
        $dataFilter = new ActiveDataFilter([
            'searchModel' => $this,
        ]);
        if ($dataFilter->load($params)) {
            $filter = $dataFilter->build();
            if ($filter === false) {
                return $dataFilter;
            }
        }

        if (!empty($filter)) {
            $query->andFilterWhere($filter);
        }

        return $dataProvider;
    }
}