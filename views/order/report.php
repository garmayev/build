<?php

use app\models\Order;
use app\models\Report;
use yii\web\View;
use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;

/**
 * @var $this View
 * @var $order Order
 * @var $model Report
 */

$this->title = \Yii::t('app', 'Add Report');

$this->params['breadcrumbs'][] = ['label' => \Yii::t('app', 'Orders'), 'url' => ['/order/index']];
$this->params['breadcrumbs'][] = ['label' => \Yii::t('app', 'Order #{id}', ['id' => $order->id]), 'url' => ['/order/view', 'id' => $order->id]];
$this->params['breadcrumbs'][] = $this->title;

$form = ActiveForm::begin([]);

echo $form->field($model, 'comment')->textarea();

echo $form->field($model, 'files[]')->fileInput(['multiple' => true]);

echo $form->field($model, 'order_id')->hiddenInput(['value' => $order->id])->label(false);

echo Html::submitButton(\Yii::t('app', 'Save'), ['class' => 'btn btn-primary']);

ActiveForm::end();