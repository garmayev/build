<?php

use app\models\Coworker;
use app\models\Dimension;
use app\models\Property;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\web\View;
use yii\widgets\ActiveForm;
use app\models\Category;
use yii\helpers\ArrayHelper;

/**
 * @var View $this
 * @var Coworker $model
 * @var ActiveForm $form
 */

$this->registerCssFile("https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css");
$this->registerJsFile("https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js", [
    'depends' => \yii\web\JqueryAsset::class
]);
?>

    <div class="coworker-form">

        <?php $form = ActiveForm::begin(); ?>

        <?= $form->field($model, 'user_id')->dropDownList(
            ArrayHelper::map(\app\models\User::find()->all(), 'id', 'name')
        )->label(false) ?>

        <div id="new">
            <div class="row" id="user">
                <div class="col-4">

                    <?= $form->field($model, 'user[profile][first_name]')->textInput()->label(\Yii::t('app', 'First Name')) ?>

                </div>
                <div class="col-4">

                    <?= $form->field($model, 'user[profile][last_name]')->textInput()->label(\Yii::t('app', 'Last Name')) ?>

                </div>
                <div class="col-4">

                    <?= $form->field($model, 'user[profile][patronymic]')->textInput()->label(\Yii::t('app', 'Patronymic')) ?>

                </div>
            </div>
            <div class="row" id="profile">
                <?php
                $isNew = $model->isNewRecord;
                ?>
                <div class="col-<?= $isNew ? 4 : 6 ?>">

                    <?= $form->field($model, 'user[username]')->textInput(['disabled' => isset($model->user)])->label(\Yii::t('app', 'Login')) ?>

                </div>
                <div class="col-<?= $isNew ? 4 : 6 ?>">

                    <?= $form->field($model, 'user[email]')->textInput(['disabled' => isset($model->user)])->label(\Yii::t('app', 'Email')) ?>

                </div>
                <?php
                if ($isNew) {
                    ?>
                    <div class="col-4">

                        <?= $form->field($model, 'user[password]')->passwordInput(['disabled' => isset($model->user)])->label(\Yii::t('app', 'Password')) ?>

                    </div>
                    <?php
                }
                ?>
            </div>
        </div>

        <?= $form->field($model, 'category_id')->dropDownList(ArrayHelper::map(Category::find()->all(), 'id', 'title'))->label(\Yii::t('app', 'Category')) ?>

        <?php
        if (!$model->isNewRecord) {
            ?>

            <div class="form-group">
                <span class="btn btn-primary" id="add-property"><?= \Yii::t('app', 'Add Property') ?></span>
            </div>

            <div id="property-list">
                <?php
                foreach ($model->coworkerProperties as $key => $coworkerProperty) {
                    ?>
                    <div class="row" data-key="<?= $key ?>">
                        <div class="col-4">
                            <?= $form
                                ->field($model, "coworkerProperties[$key][property_id]")
                                ->dropDownList(ArrayHelper::map(Property::find()->all(), 'id', 'title'), [
                                    'id' => "coworker-properties-$key-property-property_id",
                                    'class' => 'property form-control'
                                ])
                            ?>
                        </div>
                        <div class="col-4">
                            <?= $form->field($model, "coworkerProperties[$key][value]")->textInput([
                                'type' => 'number',
                                'class' => 'value form-control',
                                'id' => "coworker-properties-$key-value"
                            ]) ?>
                        </div>
                        <div class="col-3">
                            <?= $form
                                ->field($model, "coworkerProperties[$key][dimension_id]")
                                ->dropDownList(ArrayHelper::map(Dimension::find()->all(), 'id', 'title'), [
                                    'id' => "coworker-properties-$key-property-dimension_id",
                                    'class' => 'dimension form-control'
                                ]) ?>
                        </div>
                        <div class="col-1 d-flex justify-content-center align-items-center">
                            <a href="#" class="fas fa-trash"></a>
                        </div>
                    </div>
                    <?php
                }
                ?>
            </div>

            <?php
        }
        ?>

        <?= $form->field($model, 'isset_user')->checkbox(['label' => \Yii::t('app', 'Already registered?'), 'class' => 'mb-3']) ?>

        <div class="form-group">
            <?= Html::submitButton(Yii::t('app', 'Save'), ['class' => 'btn btn-success']) ?>
        </div>

        <?php ActiveForm::end(); ?>
        <div class="row example" id="example">
            <div class="col-4">
                <?= $form
                    ->field($model, 'coworkerProperties[][property_id]')
                    ->dropDownList(ArrayHelper::map(Property::find()->all(), 'id', 'title'), [
                        'id' => "coworker-properties--property_id",
                        'class' => 'property_example form-control',
                        'style' => 'display: none;'
                    ]) ?>
            </div>
            <div class="col-4">
                <?= $form
                    ->field($model, 'coworkerProperties[][value]')
                    ->textInput([
                        'type' => 'number',
                        'class' => 'value_example form-control',
                        'id' => "coworker-properties--value"
                    ]) ?>
            </div>
            <div class="col-3">
                <?= $form
                    ->field($model, 'coworkerProperties[][dimension_id]')
                    ->dropDownList(ArrayHelper::map(Dimension::find()->all(), 'id', 'title'), [
                        'id' => "coworker-properties--property-dimension_id",
                        'class' => 'dimension_example form-control'
                    ]) ?>
            </div>
            <div class="col-1 d-flex justify-content-center align-items-center">
                <a href="#" class="fas fa-trash"></a>
            </div>
        </div>
    </div>
<?php
$this->registerCss(<<<CSS
.field-coworker-user_id {
    display: none;
}
.carousel-inner {
    display: block;
    height: 260px;
}
#example {
    display: none;
}
.select2-container {
    width: 100% !important;
}
.select2-container:nth-child(2) {
    display: none;
}
CSS
);

$this->registerJsVar('index', count($model->properties));
$t = Json::encode([
    'property.placeholder' => \Yii::t('app', 'Search for a category ...')
]);

$this->registerJs(<<<JS
yii.t = $t

const propertySelectConfig = {
    ajax: {
        url: `/api/property/by-category?id=` + $('#coworker-category_id').val(),
        dataType: 'json'
    },
    placeholder: yii.t['property.placeholder'],
    minimumResultsForSearch: Infinity,
    templateSelection: (item) => {
        return item.text;
    },
    templateResult: (item) => {
        return item.title
    }
};

$(".property").select2(propertySelectConfig);

const timeout = 500;
const checkbox = $('#coworker-isset_user');

function removeItem(e)
{
    $(`.example[data-key=\${\$(e.target).attr('data-target')}]`).remove();
}
$('.fa-trash').on('click', removeItem)

$("#add-property").on("click", function () {
    let clone = $("#example").clone().attr('id', '');
    clone.attr('data-key', index);
    clone.find('.fa-trash').attr('data-target', index).on('click', removeItem)
    clone.find('.property_example').attr('id', `coworker-properties-\${index}-property_id`).toggleClass('property_example').toggleClass('property').attr('name', `Coworker[coworkerProperties][\${index}][property_id]`).select2(propertySelectConfig)
    clone.find('.value_example').attr('id', `coworker-properties-\${index}-value`).attr('name', `Coworker[coworkerProperties][\${index}][value]`).toggleClass('value_example').toggleClass('value')
    clone.find('.dimension_example').attr('id', `coworker-properties-\${index}-dimension_id`).attr('name', `Coworker[coworkerProperties][\${index}][dimension_id]`).toggleClass('dimension_example').toggleClass('dimension')
    index++;
    $("#property-list").append(clone)
})

function clickHandler(value) {
    if (value) {
        $('.field-coworker-user_id').show(timeout);
        $('#new').hide(timeout);
    } else {
        $('.field-coworker-user_id').hide(timeout);
        $('#new').show(timeout);
    }
}

clickHandler(checkbox.is(":checked"));
checkbox.on('change', function(e) {
    clickHandler($(this).is(":checked"))
})
JS
);