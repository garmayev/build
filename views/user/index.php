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
        'username',
        'email',
        'family',
        'name',
        'surname'
    ]
]);