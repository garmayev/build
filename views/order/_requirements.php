<?php

use app\models\Category;
use kartik\depdrop\DepDrop;
use kartik\select2\Select2;
use yii\bootstrap5\Html;
use yii\helpers\Url;
use yii\web\View;

/**
 * @var $this View
 * @var $index int
 * @var $item array|null Массив с данными для редактирования (если передано)
 */

$this->registerJsVar('presetData', $item);

$form = \yii\bootstrap5\ActiveForm::begin();
?>
    <div class="row">
        <div class="form-group">
            <?= Select2::widget([
                'data' => \yii\helpers\ArrayHelper::map(Category::find()->all(), 'id', 'title'),
                'name' => 'order[requirement]category_id',
                'id' => 'category_id',
                'options' => [
                    'placeholder' => \Yii::t('app', 'Select category'),
                    'class' => "mb-3 form-control",
                ],
                'pluginOptions' => [
                    'allowClear' => true,
                    'dropdownParent' => '#requirement',
                ],
            ]);
            ?>
        </div>
        <div class="form-group">
            <?= Html::textInput('order[requirement]count', '',[
                'type' => 'number',
                'id' => 'count',
                'class' => 'form-control',
                'placeholder' => \Yii::t('app', 'Count'),
                'disabled' => true,
            ]) ?>
        </div>
        <div class="form-group">
            <?= DepDrop::widget([
                'type' => DepDrop::TYPE_SELECT2,
                'select2Options' => [
                    'pluginOptions' => [
                        'allowClear' => true,
                        'dropdownParent' => '#requirement',
                        'placeholder' => \Yii::t('app', 'Select property'),
                    ],
                ],
                'options' => ['id' => 'property_id', 'class' => 'form-control'],
                'name' => 'order[requirement]property_id',
                'pluginOptions' => [
                    'depends' => ['category_id'],
                    'placeholder' => \Yii::t('app', 'Select property'),
                    'url' => Url::to(['/property/by-category']),
                ],
            ]);
            ?>
        </div>
        <div class="row">
            <div class="form-group col-4">
                <?= Html::dropDownList('order[requirement]type', '', [
                    'more' => \Yii::t('app', 'More'),
                    'less' => \Yii::t('app', 'Less'),
                    'equal' => \Yii::t('app', 'Equal'),
                    'not-equal' => \Yii::t('app', 'Not Equal'),
                ], [
                    'disabled' => true,
                    'id' => 'type',
                    'class' => 'form-control'
                ]) ?>
            </div>
            <div class="form-group col-4">
                <?= Html::textInput('order[requirement]value', '', [
                    'type' => 'number',
                    'disabled' => true,
                    'placeholder' => \Yii::t('app', 'Value'),
                    'id' => 'value',
                    'class' => 'form-control'
                ]) ?>
            </div>
            <?=  Html::hiddenInput('hidden_property_id', $item['property_id'] ?? null, ['id' => 'hidden_property_id'])?>
            <div class="form-group col-4">
                <?= DepDrop::widget([
                    'type' => DepDrop::TYPE_SELECT2,
                    'select2Options' => [
                        'pluginOptions' => [
                            'allowClear' => true,
                            'dropdownParent' => '#requirement',
                            'placeholder' => \Yii::t('app', 'Select dimension'),
                        ],
                    ],
                    'options' => ['id' => 'dimension_id', 'class' => 'form-control'],
                    'name' => 'order[requirement]dimension_id',
                    'pluginOptions' => [
                        'depends' => ['property_id'],
                        'initialize' => isset($item['dimension_id']) ? true : false,
                        'placeholder' => \Yii::t('app', 'Select dimension'),
                        'url' => Url::to(['/dimension/by-property']),
                        'params' => ['hidden_property_id']
                    ]
                ]);
                ?>
            </div>
        </div>
    </div>
<?php
\yii\bootstrap5\ActiveForm::end();

// Упрощенный JavaScript для инициализации
$this->registerJs(<<<JS
window.categoryIdField = $('#category_id');
window.propertyIdField = $('#property_id');
window.countField = $('#count');
window.typeField = $('#type');
window.dimensionIdField = $('#dimension_id');
window.valueField = $('#value');

window.categoryIdField.on('select2:select', function() {
    window.countField.attr('disabled', false);
    window.propertyIdField.attr('disabled', false);
    if (presetData.count && presetData.property_id) {
        window.countField.val(presetData.count);
        window.propertyIdField.val(presetData.property_id)
            .trigger('change')
            .trigger({type: 'change', value: presetData.property_id})
            .trigger('depdrop:change')
            .trigger({type: 'depdrop:change', value: presetData.property_id});
    }
})
window.propertyIdField.on('depdrop:afterChange', function(event) {
    $(event.target).val(presetData.property_id ?? null)
    const isDisabled = $(event.currentTarget).val() === '';
    window.typeField.attr('disabled', isDisabled);
    window.dimensionIdField.attr('disabled', isDisabled);
    window.valueField.attr('disabled', isDisabled);
    window.dimensionIdField
        .trigger('change')
        .trigger({type: 'change', value: presetData.dimension_id})
        .trigger('depdrop:change')
        .trigger({type: 'depdrop:change', value: presetData.dimension_id});
})
window.dimensionIdField.on('depdrop:afterChange', function(event) {
    window.dimensionIdField.val(presetData.dimension_id ?? null)
})

if (Object.keys(presetData).length) {
    window.categoryIdField.val(presetData.category_id).trigger('change').trigger({type: 'change', value: presetData.category_id}).trigger({type: 'select2:select', value: presetData.category_id});
    window.countField.val(presetData.count).trigger('change');
    window.propertyIdField.val(presetData.property_id).on('depdrop:ready', function(e) {
        console.log(e)  
    }).trigger('change').trigger({type: 'change', value: presetData.property_id});
    window.typeField.val(presetData.type);
    window.valueField.val(presetData.value);
    window.dimensionIdField.val(presetData.dimension_id).trigger('change').trigger({type: 'change', value: presetData.dimension_id});
}
JS);