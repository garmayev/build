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

$this->params['breadcrumbs'][] = [
    'label' => \Yii::t('app', 'Orders'),
    'url' => ['/order/index']
];
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

echo $form->field($model, 'datetime')->textInput(['type' => 'date', 'value' => \Yii::$app->formatter->asDate($model->date, 'php:Y-m-d')])->label(\Yii::t('app', 'Select date'));

echo $form->field($model, 'mode')->widget(\kartik\select2\Select2::classname(), [
    'data' => [
        Order::MODE_SINGLE_FIXED => \Yii::t('app', 'mode_single_fixed'),
        Order::MODE_LONG_FIXED => \Yii::t('app', 'mode_long_fixed'),
        Order::MODE_LONG_DAILY => \Yii::t('app', 'mode_long_daily'),
    ],
]);

echo $form->field($model, 'price', [
    'options' => [
        'style' => $model->mode === Order::MODE_LONG_DAILY ? "display: none;" : ""
    ]
])->widget(\yii\widgets\MaskedInput::className(), [
    'clientOptions' => [
        'alias' => 'currency',
        'radixPoint' => '.',
        'groupSeparator' => '',
        'digits' => 2,
        'rightAlign' => false,
        'autoGroup' => true,
        'autoUnmask' => true, // Автоматически убирает маску при отправке
    ]
]);

if (count($model->attachments)) {
    echo \yii\grid\GridView::widget([
        'dataProvider' => new \yii\data\ArrayDataProvider([
            'allModels' => $model->attachments,
        ]),
        'summary' => false,
        'tableOptions' => [
            'class' => 'table table-striped',
        ],
        'columns' => [
            [
                'attribute' => 'url',
                'format' => 'raw',
                'value' => function ($model) {
                    return Html::a($model->url, $model->url);
                }
            ]
        ]
    ]);
}

echo $form->field($model, 'files[]')->fileInput([
    'multiple' => true,
])->label(\Yii::t('app', 'Attachments'));

echo Html::tag('div', '', ['id' => 'preview']);

echo $form->field($model, 'comment')->textarea(['rows' => 6]);

echo Html::tag('div', '', ['class' => 'dynamicTable', 'data-index' => $model->id, 'data-lang' => \Yii::$app->language, 'data-is-new' => $model->isNewRecord ? "true" : "false", "data-token" => \Yii::$app->user->identity->access_token]);

echo Html::submitButton(\Yii::t('app', 'Save'), ['class' => 'btn btn-success']);

ActiveForm::end();

$this->registerJs(<<<JS
$('#order-mode').on('change', function() {
    const value = $(this).val();
    if (Number(value) === 2) {
        $('.field-order-price').hide();
    } else {
        $('.field-order-price').show();
    }
})
$('#order-files').on('change', function() {
    let preview = document.querySelector('#preview');
    preview.innerHTML = ''; // Clear previous previews

    if (this.files) {
        Array.from(this.files).forEach(function(file) {
            if (file.type.startsWith('image/')) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    let container = document.createElement("div");
                    container.classList.add('img-container')
                    var img = document.createElement('img');
                    img.src = e.target.result;
                    img.classList.add('img-preview');
                    container.appendChild(img);
                    preview.appendChild(container);
                };
                reader.readAsDataURL(file);
            }
        });
    }
})
// Очистка поля цены перед отправкой формы
$('#order-form').on('submit', function() {
    const priceField = $('#order-price');
    if (priceField.length) {
        // Убираем пробелы из поля цены
        priceField.val(priceField.val().replace(/\s/g, ''));
    }
});
JS
);

$this->registerCss(<<<CSS
#preview {
    display: grid;
    grid-template-columns: repeat(12, 1fr);
    grid-template-rows: 1fr;
    grid-column-gap: 10px;
    grid-row-gap: 10px;
}
.img-container {
    display: flex;
    justify-content: center;
    align-items: center;
    border: 1px solid #ccc;
    padding: 10px;
}
.img-preview {
    max-width: 150px;
    max-height: 150px;
}
CSS
);