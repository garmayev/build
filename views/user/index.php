<?php

use app\models\search\UserSearch;
use yii\data\ActiveDataProvider;
use yii\web\View;
use yii\grid\GridView;
use yii\helpers\Html;

/**
 * @var $this View
 * @var $searchModel UserSearch
 * @var $dataProvider ActiveDataProvider
 */

echo GridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel' => $searchModel,
    'summary' => false,
    'tableOptions' => [
        'class' => 'table table-striped'
    ],
    'columns' => [
        [
            'attribute' => 'username',
            'label' => \Yii::t('app', 'Username'),
        ],
        [
            'attribute' => 'email',
            'format' => 'email',
            'label' => \Yii::t('app', 'Email'),
        ],
        [
            'attribute' => 'profile.fullName',
            'label' => \Yii::t('app', 'Name'),
            'value' => function (\app\models\User $model) {
                return !empty($model->profile->fullName) ? $model->profile->fullName : null;
            }
        ],
        [
            'attribute' => 'phone',
            'label' => \Yii::t('app', 'Phone'),
            'format' => 'raw',
            'value' => function (\app\models\User $model) {
                return !empty($model->profile->phone) ? \floor12\phone\PhoneFormatter::a($model->profile->phone) : null;
            }
        ], [
            'attribute' => 'status',
            'label' => \Yii::t('app', 'Status'),
            'format' => 'raw',
            'value' => function (\app\models\User $model) {
                return Html::dropDownList('status', $model->status, $model->statusList, ['class' => ['form-control', 'status'], 'data-key' => $model->id]);
            }
        ]
    ]
]);

$this->registerJs(<<<JS
$(() => {
    $(".form-control.status").on('change', function(e) {
        console.log(e)
    })
})
JS);