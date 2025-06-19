<?php

use app\models\Profile;
use yii\web\View;
use yii\widgets\DetailView;


/**
 * @var $this View
 * @var $model Profile
 */

echo DetailView::widget([
    'model' => $model,
    'options' => ['class' => 'table table-striped detail-view'],
    'attributes' => [
        'family',
        'name',
        'surname',
        'birthday:date',
        [
            'attribute' => 'phone',
            'value' => function(Profile $model){
                if ($model->phone) {
                    return \floor12\phone\PhoneFormatter::format($model->phone);
                }
                return null;
            }
        ], [
            'label' => \Yii::t('app', 'Social'),
            'format' => 'raw',
            'value' => function(Profile $model){
                $result = $model->chat_id ? "<i class='fas fa-telegram'></i>" : "";
                $result .= $model->device_id ? "<i class='fas fa-mobile'></i>" : "";
                return $result;
            }
        ]
    ]
]);