<?php

use yii\web\View;
use yii\grid\GridView;
use yii\data\ArrayDataProvider;

/**
 * @var $this View
 *
 */

echo GridView::widget([
    'dataProvider' => new ArrayDataProvider([
        'allModels' => \Yii::$app->max->getWebhook(),
    ])
]);