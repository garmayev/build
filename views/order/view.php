<?php

use app\models\Order;
use yii\data\ArrayDataProvider;
use yii\grid\GridView;
use yii\helpers\Html;
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
    'model' => $model,
    'attributes' => [
        'building.title',
        'building.location.address',
        [
            'attribute' => 'date',
            'value' => function ($model) {
                return Yii::$app->formatter->asDate($model->date, 'php:d m Y');
            }
        ],
        [
            'attribute' => 'status',
            'value' => function (Order $model) {
                return $model->statusTitle;
            }
        ],
        'typeName',
        'comment',
        [
            'attribute' => 'attachments',
            'label' => \Yii::t('app', 'Attachments'),
            'format' => 'raw',
            'value' => function (Order $model) {
                $result = "";
                foreach ($model->attachments as $attachment) {
                    $result .= Html::tag('p', Html::a($attachment->url, $attachment->url, ['target' => '_blank']));
                }
                return $result;
            }
        ],
    ],
    'options' => [
        'class' => 'table table-striped'
    ]
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
    'tableOptions' => [
        'class' => 'table table-striped'
    ]
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
            [
                'attribute' => 'name',
                'label' => \Yii::t('app', 'Coworkers'),
                'value' => function (\app\models\Coworker $model) {
                    return "{$model->lastname} {$model->firstname}";
                }
            ], [
                'attribute' => 'coworkerProperties',
                'label' => \Yii::t('app', 'Properties'),
                'format' => 'raw',
                'value' => function (app\models\Coworker $model) {
                    $result = "";
                    foreach ($model->coworkerProperties as $coworkerProperty) {
                        $result .= Html::tag("p", "{$coworkerProperty->property->title} {$coworkerProperty->value} {$coworkerProperty->dimension->title}");
                    }
                    return $result;
                }
            ],
        ],
        'tableOptions' => [
            'class' => 'table table-striped'
        ]
    ]);
}
