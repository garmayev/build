<?php

use app\models\Coworker;
use app\models\CoworkerProperty;
use kartik\depdrop\DepDrop;
use kartik\select2\Select2;
use yii\bootstrap4\Modal;
use yii\helpers\Html;
use yii\helpers\Url;
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
?>
    <div class="nav nav-tabs" id="tabs" role="tabList">
        <a class="nav-link active" href="#account" data-toggle="tab" role="tab" aria-selected="true">
            <?= \Yii::t('app', 'Account') ?>
        </a>
        <a class="nav-link <?= ($model->isNewRecord) ? 'disabled' : '' ?>" href="#profile" data-toggle="tab" role="tab"
           aria-selected="false">
            <?= \Yii::t('app', 'Profile') ?>
        </a>
        <a class="nav-link <?= ($model->isNewRecord) ? 'disabled' : '' ?>" href="#properties" data-toggle="tab"
           role="tab" aria-selected="false">
            <?= \Yii::t('app', 'Properties') ?>
        </a>
    </div>
    <div class="tab-content mb-3" id="tabContent">
        <div class="tab-pane fade show active" id="account">
            <?php
            $userModel = new \app\models\forms\UserRegisterForm();
            $form = ActiveForm::begin([
                'enableAjaxValidation' => true,
                'validationUrl' => Url::toRoute(['/user/validate-register'])
            ]);
            ?>
            <div class="p-3">
                <?php
                $isNew = $model->isNewRecord;
                echo $form->field($userModel, 'username')->textInput()->label(\Yii::t('app', 'Username'));
                echo $form->field($userModel, 'email')->textInput()->label(\Yii::t('app', 'Email'));
                if ($isNew) {
                    echo $form->field($userModel, 'new_password')->passwordInput()->label(\Yii::t('app', 'New Password'));
                }
                ?>
                <p class="d-flex justify-content-end">
                    <a class="btn btn-success next-submit" href="#profile" data-target="#profile" data-toggle="tab"
                       role="tab" aria-selected="false">
                        <?= \Yii::t('app', 'Next') ?>
                    </a>
                </p>
            </div>
            <?php
            ActiveForm::end();
            ?>
        </div>
        <div class="tab-pane fade" id="profile">
            <?php
            $form = ActiveForm::begin([
                'enableAjaxValidation' => true,
                'validationUrl' => Url::toRoute(['/user/profile-register'])
            ]);
            ?>
            <div class="p-3">
                <?php
                echo $form->field($model, 'user[profile][first_name]')->textInput()->label(\Yii::t('app', 'First Name'));
                echo $form->field($model, 'user[profile][last_name]')->textInput()->label(\Yii::t('app', 'Last Name'));
                echo $form->field($model, 'user[profile][patronymic]')->textInput()->label(\Yii::t('app', 'Patronymic'));
                echo $form->field($model, 'user[profile][birthday]')->textInput(['type' => 'date'])->label(\Yii::t('app', 'Birthday'));
                echo $form->field($model, 'user[profile][biography]')->textarea()->label(\Yii::t('app', 'Biography'));
                ?>
                <p class="d-flex justify-content-between">
                    <a class="btn btn-default next-submit" href="#account" data-target="#account" data-toggle="tab"
                       role="tab" aria-selected="false">
                        <?= \Yii::t('app', 'Back') ?>
                    </a>
                    <a class="btn btn-success next-submit" href="#properties" data-target="#properties"
                       data-toggle="tab"
                       role="tab" aria-selected="false">
                        <?= \Yii::t('app', 'Next') ?>
                    </a>
                </p>
            </div>
            <?php
            ActiveForm::end();
            ?>
        </div>
        <div class="tab-pane fade" id="properties">
            <div class="p-3">
                <?php
                echo Html::beginTag('div', ['class' => 'form-group']);
                echo Html::label(\Yii::t('app', 'Category'), 'category_id', ['class' => 'control-label']);
                echo Select2::widget([
                    'id' => 'category_id',
                    'name' => 'category_id',
                    'pluginOptions' => [
                        'ajax' => [
                            'url' => '/api/category/index',
                            'dataType' => 'json',
                            'data' => $terms
                        ],
                        'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
                        'templateResult' => new JsExpression('function(city) { return city.title; }'),
                        'templateSelection' => new JsExpression('function (city) { return city.title; }'),
                    ]
                ]);
                echo Html::endTag('div');

                echo Html::tag('button', \Yii::t('app', 'Add Property'), [
                    'class' => 'btn btn-primary mb-3',
                    'type' => 'button',
                    'data' => [
                        'toggle' => 'modal',
                        'target' => '#addProperty',
                    ]
                ]);
                ?>
                <div id="dynamicTable"></div>
                <p class="w-100 d-flex justify-content-between">
                    <a class="btn btn-default next-submit" href="#profile" data-target="#profile" data-toggle="tab"
                       role="tab" aria-selected="false">
                        <?= \Yii::t('app', 'Back') ?>
                    </a>
                    <button class="btn btn-success"><?= \Yii::t('app', 'Save') ?></button>
                </p>
            </div>
        </div>
    </div>
<?php
$cProperty = new CoworkerProperty();
Modal::begin([
    'id' => 'addProperty',
    'title' => \Yii::t('app', 'Add Property'),
    'size' => Modal::SIZE_LARGE,
    'footer' => Html::beginTag('div', ['class' => 'w-100 d-flex justify-content-between']).
        Html::tag('button', \Yii::t('app', 'Cancel'), ['class' => 'btn btn-secondary', 'type' => 'button', 'data-dismiss' => 'modal']).
        Html::tag('button', \Yii::t('app', 'Apply'), ['class' => 'btn btn-success', 'type' => 'button', 'id' => 'btn-apply']).
        Html::endTag('div')
]);

$propertyForm = ActiveForm::begin();

echo $propertyForm->field($cProperty, 'property_id')->widget(DepDrop::class, [
    'pluginOptions' => [
        'depends' => [
            'category_id',
        ],
        'url' => "/api/property/by-category",
    ]
])->label(\Yii::t('app', 'Property'));

echo $propertyForm->field($cProperty, 'value')->textInput()->label(\Yii::t('app', 'Value'));

echo $propertyForm->field($cProperty, 'dimension_id')->widget(DepDrop::class, [
    'type' => DepDrop::TYPE_SELECT2,
    'pluginOptions' => [
        'depends' => [Html::getInputId($cProperty, 'property_id')],
        'url' => "/api/dimension/by-property",
    ]
])->label(\Yii::t('app', 'Dimension'));

ActiveForm::end();

Modal::end();

$this->registerJs(<<<JS
$(() => {
    $('#btn-apply').click(function () {
        const form = $('.modal').find('form input, form select');
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
