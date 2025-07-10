<?php

use app\models\Building;
use app\models\Order;
use kartik\datetime\DateTimePicker;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\web\View;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/**
 * @var View $this
 * @var Order $model
 */

//\app\assets\ReactAsset::register($this);

$form = ActiveForm::begin([
    'options' => [
        'enctype' => 'multipart/form-data'
    ]
]);

$this->title = \Yii::t('app', 'Order coworker');

$this->params['breadcrumbs'][] = [
    'label' => \Yii::t('app', 'Orders'),
    'url' => ['/order/index']
];

$this->registerJsVar('token', \Yii::$app->user->identity->access_token);

$this->params['breadcrumbs'][] = $this->title;

echo $form->field($model, 'status')->dropDownList($model->statusList);

echo $form->field($model, 'type')->hiddenInput(['value' => Order::TYPE_COWORKER])->label(false);

echo $form->field($model, 'building_id')->dropDownList(
    ArrayHelper::map(
        Building::find()->where(['user_id' => \Yii::$app->user->id])->all(),
        'id',
        'title',
    )
)->label(\Yii::t('app', 'Select building'));

echo $form->field($model, 'datetime')->widget(DateTimePicker::class, [
    'convertFormat' => true,
    'pluginOptions' => [
        'autoclose' => true,
        'startDate' => date('Y.m.d'),
        'minView' => 2,
        'format' => 'dd.MM.yyyy',
        'daysOfWeekDisabled' => [0, 6],
    ]
])->label(\Yii::t('app', 'Select date'));

echo $form->field($model, 'files[]')->fileInput([
    'multiple' => true,
])->label(\Yii::t('app', 'Attachments'));

echo $form->field($model, 'comment')->textarea(['rows' => 6]);

echo Html::tag('div', '', ['class' => 'dynamicTable', 'data-content' => $model->requirements]);

echo Html::submitButton(\Yii::t('app', 'Save'), ['class' => 'btn btn-success']);

ActiveForm::end();