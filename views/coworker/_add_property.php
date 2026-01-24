<?php

use kartik\depdrop\DepDrop;
use kartik\select2\Select2;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;

/**
 * @var $this yii\web\View
 * @var $model app\models\UserProperty
 */

$data = \Yii::$app->request->get();
if ($data) {
    $model->category_id = $data['category_id'];
    $model->property_id = $data['property_id'];
    $model->dimension_id = $data['dimension_id'];
    $model->value = $data['value'];
    $this->registerJsVar('presetData', $data);
} else {
    $this->registerJsVar('presetData', []);
}

$form = ActiveForm::begin([
    'id' => 'property-form',
    'enableAjaxValidation' => false,
]);

echo Html::beginTag('div', ['id' => 'propertyModal', 'class' => 'row']);

echo $form->field($model, 'category_id', [
    'options' => [
        'class' => 'col-12'
    ]
])->widget(
    Select2::class,
    [
        'id' => 'category_id',
        'class' => 'form-control',
        'data' => ArrayHelper::map(\app\models\Category::find()->all(), 'id', 'title'),
        'options' => ['placeholder' => \Yii::t('app', 'Select category')],
        'pluginOptions' => [
            'dropdownParent' => '#propertyModal',
            'allowClear' => true
        ],
    ]
)->label(\Yii::t('app', 'Category'));

echo $form->field($model, 'property_id', [
    'options' => [
        'class' => 'col-4'
    ]
])->widget(DepDrop::class,
    [
        'id' => 'property_id',
        'class' => 'form-control',
        'type' => DepDrop::TYPE_SELECT2,
        'pluginOptions' => [
            'dropdownParent' => '#propertyModal',
            'depends' => ['userproperty-category_id'],
            'initialize' => isset($model->property_id),
            'placeholder' => \Yii::t('app', 'Select property'),
            'url' => ['/coworker/get-properties'],
        ]
    ]
)->label(\Yii::t('app', 'Property'));

echo $form->field($model, 'value', [
    'options' => [
        'class' => 'col-4'
    ]
])->textInput([
    'type' => 'number',
    'id' => 'userproperty-value',
    'class' => 'form-control',
    'disabled' => true
])->label(\Yii::t('app', 'Value'));

// Простое поле вместо DepDrop
echo $form->field($model, 'dimension_id', [
    'options' => [
        'class' => 'col-4'
    ]
])->widget(DepDrop::class,
    [
        'id' => 'dimension_id',
        'class' => 'form-control',
        'type' => DepDrop::TYPE_SELECT2,
        'pluginOptions' => [
            'dropdownParent' => '#propertyModal',
            'depends' => ['userproperty-value', 'userproperty-property_id'],
            'placeholder' => \Yii::t('app', 'Select dimension'),
            'initialize' => isset($model->dimension_id),
            'url' => ['/coworker/get-dimensions'],
        ]
    ]
)->label(\Yii::t('app', 'Dimension'));

echo Html::endTag('div');

ActiveForm::end();

// JavaScript для управления состоянием полей
$this->registerJs(<<<JS
$("#userproperty-property_id").on('change', (event) => {
    if ($(event.currentTarget).val()) {
        $("#userproperty-value").prop("disabled", false);
    }
})

console.log(presetData)

if (presetData.length > 0) {
    $("#userproperty-category_id").val(presetData.category_id).trigger("select2:select");
    $("#userproperty-property_id").on('depdrop:ready', () => {
        console.log('debug')
        $(this).val(presetData.property_id).trigger("depdrop:change");
    })
    $("#userproperty-value").prop("disabled", false);
    $("#userproperty-dimension_id").val(presetData.dimension_id).trigger("depdrop:change");
}
JS
);