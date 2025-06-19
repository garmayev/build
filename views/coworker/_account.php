<?php
use app\models\User;
use yii\web\View;


/**
 * @var $this View
 * @var $model User
 */

echo \yii\widgets\DetailView::widget([
    'model' => $model,
    'options' => ['class' => 'table table-striped detail-view'],
    'attributes' => [
        'username',
        'email:email',
        'statusName',
        'referrer.name'
    ]
]);