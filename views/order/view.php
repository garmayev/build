<?php

use app\models\Order;
use yii\data\ArrayDataProvider;
use yii\grid\GridView;
use yii\web\View;
use yii\widgets\DetailView;

/**
 * @var View $this
 * @var Order $model
 */

$this->title = \Yii::t('app', 'View Order: {name}', ['name' => "#{$model->id}"]);

$this->params['breadcrumbs'][] = ['label' => \Yii::t('app', 'Orders'), 'url' => ['/order/index']];
$this->params['breadcrumbs'][] = $this->title;

echo DetailView::widget([
    'model' => $model
]);

echo GridView::widget([
    'dataProvider' => new ArrayDataProvider([
        'allModels' => $model->filters
    ]),
    'summary' => false,
    'columns' => [
        'category.title',
        'count',
        [
            'attribute' => 'requirements',
            'label' => \Yii::t('app', 'Requirement'),
            'format' => 'raw',
            'value' => function (\app\models\Filter $model) {
                $result = '';
                foreach ($model->requirements as $requirement) {
                    $result .= "<p>{$requirement->property->title} {$requirement->type} {$requirement->value} {$requirement->dimension->title}</p>";
                }
                return $result;
            }
        ],
    ],
]);


$data = [];

switch ($model->type) {
    case Order::TYPE_COWORKER:
        $data = $model->coworkers;
        break;
}

if (count($data)) {

    echo GridView::widget([
        'dataProvider' => new ArrayDataProvider([
            'allModels' => $data
        ]),
        'summary' => false,
        'columns' => [
            'user.name'
        ],
    ]);
}