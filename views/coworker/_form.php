<?php

use app\models\Category;
use app\models\Coworker;
use kartik\depdrop\DepDrop;
use kartik\select2\Select2;
use yii\bootstrap4\Modal;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\web\JsExpression;
use yii\web\View;
use yii\widgets\ActiveForm;

/**
 * @var View $this
 * @var Coworker $model
 * @var ActiveForm $form
 */

$this->registerCssFile("https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css");
$this->registerJsFile("https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js", [
    'depends' => \yii\web\JqueryAsset::class
]);
$terms = new JsExpression('function(params) { return {q:params.term}; }');
$form = ActiveForm::begin([]);

echo $form->field($model, "lastname")->textInput()->label(\Yii::t('app', 'Last Name'));
echo $form->field($model, "firstname")->textInput()->label(\Yii::t('app', 'First Name'));
echo $form->field($model, "email")->textInput(['type' => 'email'])->label(\Yii::t('app', 'Email'));;
echo $form->field($model, "phone")->textInput(['type' => 'phone'])->label(\Yii::t('app', 'Phone'));
echo $form->field($model, "priority")->dropDownList([
    Coworker::PRIORITY_LOW => \Yii::t('app', 'Priority low'),
    Coworker::PRIORITY_NORMAL => \Yii::t('app', 'Priority normal'),
    Coworker::PRIORITY_HIGH => \Yii::t('app', 'Priority high'),
])->label(\Yii::t('app', 'Priority'));
echo $form->field($model, "type")->dropDownList([
    Coworker::TYPE_WORKER => \Yii::t('app', 'Coworker'),
    Coworker::TYPE_CUSTOMER => \Yii::t('app', 'Customer'),
])->label(\Yii::t('app', 'Type'));
echo $form->field($model, 'files')->fileInput([
    'multiple' => true,
])->label(\Yii::t('app', 'Attachments'));
echo $form->field($model, "category_id")->dropDownList(
    ArrayHelper::map(Category::find()->all(), 'id', 'title'),
    ['prompt' => \Yii::t('app', 'Select category'), 'id' => 'category_id']
);

echo Html::tag('button', \Yii::t('app', 'Add Property'), [
    'class' => 'btn btn-primary mb-3',
    'type' => 'button',
    'data' => [
        'toggle' => 'modal',
        'target' => '#addProperty',
    ]
]);
?>
    <table class="table table-striped" id="dynamic-table">
        <thead>
        <th><?= \Yii::t('app', 'Property') ?></th>
        <th><?= \Yii::t('app', 'Value') ?></th>
        <th><?= \Yii::t('app', 'Dimension') ?></th>
        <th></th>
        </thead>
        <tbody>
        <?php
        foreach ($model->coworkerProperties as $index => $coworkerProperty) {
            echo Html::beginTag('tr', ['key' => $index]);
            echo Html::tag('td', $coworkerProperty->property->title);
            echo Html::tag('td', $coworkerProperty->value);
            echo Html::tag('td', $coworkerProperty->dimension->title);
            echo Html::tag('td',
                Html::a(
                    Html::tag('i', '', ['class' => 'fas fa-trash']),
                    '#',
                    ['class' => 'remove']
                )
            );
            echo Html::endTag('tr');
        }
        ?>
        </tbody>
    </table>
<?php
echo Html::submitButton(\Yii::t('app', 'Save'), ['class' => 'btn btn-success']);

ActiveForm::end();

Modal::begin([
    'id' => 'addProperty',
    'title' => \Yii::t('app', 'Add Property'),
    'size' => Modal::SIZE_LARGE,
    'footer' => Html::beginTag('div', ['class' => 'w-100 d-flex justify-content-between']) .
        Html::tag('button', \Yii::t('app', 'Cancel'), ['class' => 'btn btn-secondary', 'type' => 'button', 'data-dismiss' => 'modal']) .
        Html::tag('button', \Yii::t('app', 'Apply'), ['class' => 'btn btn-success', 'type' => 'button', 'id' => 'btn-apply']) .
        Html::endTag('div')
]);

echo Html::beginTag('div', ['class' => 'form-group']);
echo Html::label(\Yii::t('app', 'Property'), 'property_id', ['class' => 'control-label']);
echo DepDrop::widget([
    'name' => 'property_id',
    'id' => 'property_id',
    'pluginOptions' => [
        'depends' => [
            'category_id',
        ],
        'url' => "/api/property/by-category?id=1",
    ]
]);
echo Html::endTag('div');

echo Html::beginTag('div', ['class' => 'form-group']);
echo Html::label(\Yii::t('app', 'Value'), 'value', ['class' => 'control-label']);
echo Html::textInput('value', null, ['class' => 'form-control', 'id' => 'value']);
echo Html::endTag('div');

echo Html::beginTag('div', ['class' => 'form-group']);
echo Html::label(\Yii::t('app', 'Property'), 'dimension_id', ['class' => 'control-label']);
echo DepDrop::widget([
    'name' => 'dimension_id',
    'id' => 'dimension_id',
    'pluginOptions' => [
        'depends' => [
            'property_id',
        ],
        'url' => "/api/dimension/by-property",
    ]
]);
echo Html::endTag('div');

Modal::end();

$this->registerJs(<<<JS
$(() => {
    let index = $('tbody tr').length;
    $('#category_id').trigger('change');
    $('tbody .remove').on('click', function(e) {
        e.currentTarget.closest('tr').remove();
    })
    $('#btn-apply').click(function () {
        const modal = $(".modal");
        
        const property_id = modal.find('#property_id').val();
        const property_value = modal.find('#property_id option:selected').text();
        const value = modal.find('#value').val();
        const dimension_id = modal.find('#dimension_id').val();
        const dimension_value = modal.find('#dimension_id option:selected').text();
        
        $("#dynamic-table").find("tbody").append(`
        <tr key="\${index}">
            <td>\${property_value}<input type="hidden" value="\${property_id}" name="Coworker[coworkerProperties][\${index}][property_id]"/></td>
            <td>\${value}<input type="hidden" value="\${value}" name="Coworker[coworkerProperties][\${index}][value]"/></td>
            <td>\${dimension_value}<input type="hidden" value="\${dimension_id}" name="Coworker[coworkerProperties][\${index}][dimension_id]"/></td>
        </tr>
        `);
        index++;
        modal.modal('hide');
    })
    $(".next-submit").click(function() {
        const target = $(this).attr("data-target");
        $(`[href="\${target}"]`).removeClass("disabled").tab("show");
    })
})
JS
);
$this->registerCss(<<<CSS
.tab-pane {
    background-color: #fff;
    border-left: 1px solid #dee2e6;
    border-bottom: 1px solid #dee2e6;
    border-right: 1px solid #dee2e6;
}
CSS
);
