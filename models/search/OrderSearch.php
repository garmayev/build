<?php

namespace app\models\search;

use app\models\Order;
use Yii;
use yii\data\ActiveDataFilter;
use yii\data\ActiveDataProvider;

class OrderSearch extends Order
{
    public function search($params)
    {
        $query = Order::find()->where(['created_by' => Yii::$app->user->id]);
        \Yii::error($params);
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
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