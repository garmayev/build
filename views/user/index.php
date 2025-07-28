<?php

use app\models\search\UserSearch;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;
use yii\web\View;
use yii\grid\GridView;
use yii\helpers\Html;

/**
 * @var $this View
 * @var $searchModel UserSearch
 * @var $dataProvider ActiveDataProvider
 */

$this->title = \Yii::t('app', 'Users');

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
            'headerOptions' => ['class' => 'text-center col-3'],
            'label' => \Yii::t('app', 'Username'),
        ],
        [
            'attribute' => 'email',
            'headerOptions' => ['class' => 'text-center col-3'],
            'format' => 'email',
            'label' => \Yii::t('app', 'Email'),
        ],
        [
            'attribute' => 'fullName',
            'headerOptions' => ['class' => 'text-center col-1'],
            'label' => \Yii::t('app', 'Name'),
            'value' => function (\app\models\User $model) {
                return !empty($model->profile->fullName) ? $model->profile->fullName : $model->username;
            }
        ],
        [
            'attribute' => 'phone',
            'label' => \Yii::t('app', 'Phone'),
            'headerOptions' => ['class' => 'text-center col-1'],
            'format' => 'raw',
            'value' => function (\app\models\User $model) {
                return !empty($model->profile->phone) ? \floor12\phone\PhoneFormatter::a($model->profile->phone) : null;
            }
        ], [
            'attribute' => 'role',
            'label' => \Yii::t('app', 'Role'),
            'headerOptions' => ['class' => 'text-center col-1'],
            'format' => 'raw',
            'value' => function (\app\models\User $model) {
                $roles = \Yii::$app->authManager->getRoles();
//                \Yii::error($roles);
                $currentRole = \Yii::$app->authManager->getRolesByUser($model->id);
                return Html::dropDownList('',
                    ArrayHelper::map($currentRole, 'name', 'name'),
                    ArrayHelper::map($roles, 'name', 'name'),
                    ['class' => 'form-control role-select', 'data-key' => $model->id]
                );
            }
        ], [
            'attribute' => 'status',
            'label' => \Yii::t('app', 'Status'),
            'headerOptions' => ['class' => 'text-center col-2'],
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
        const id = e.target.getAttribute('data-key');
        const status = e.target.value;
        fetch(`/user/set-status?id=\${id}&status=\${status}`)
            .then(response => response.json())
            .then(data => {
                console.log(data)
            })
    })
    $(".form-control.role-select").on('change', function(e) {
        const id = e.target.getAttribute('data-key');
        const role = e.target.value;
        fetch(`/user/set-role?id=\${id}&role=\${role}`)
            .then(response => response.json())
            .then(data => {
                console.log(data)
            })
    })
})
JS);