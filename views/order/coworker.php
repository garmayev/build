<?php

use app\models\Building;
use app\models\Order;
use app\models\User;
use kartik\datetime\DateTimePicker;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\web\View;
use yii\helpers\Html;
use yii\bootstrap5\ActiveForm;

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

$this->title = \Yii::t('app', 'Order works');

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

//echo $form->field($model, 'datetime')->textInput(['type' => 'date', 'value' => \Yii::$app->formatter->asDate($model->date, 'php:Y-m-d')])->label(\Yii::t('app', 'Select date'));
echo $form->field($model, 'datetime')->widget(kartik\date\DatePicker::class, [
    'options' => ['placeholder' => 'Select operating time ...', 'value' => $model->date ?? date('d.m.Y')],
    'pluginOptions' => [
        'autoclose' => true,
        'startDate' => '0',
    ],
])->label(\Yii::t('app', 'Select date'));

echo $form->field($model, 'priority_level')->dropDownList([
    User::PRIORITY_LOW => \Yii::t('app', 'Priority low'),
    User::PRIORITY_NORMAL => \Yii::t('app', 'Priority normal'),
    User::PRIORITY_HIGH => \Yii::t('app', 'Priority high'),
], [
    'value' => User::PRIORITY_HIGH,
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

echo $form->field($model, 'mode')->hiddenInput(['value' => Order::MODE_LONG_DAILY])->label(false);

echo $form->field($model, 'price')->hiddenInput(['value' => $model->price ?? 0])->label(false);

echo $form->field($model, 'files[]')->fileInput([
    'multiple' => true,
])->label(\Yii::t('app', 'Attachments'));

echo $form->field($model, 'summary')->textInput();

echo $form->field($model, 'comment')->textarea(['rows' => 6]);

echo Html::tag('div', '', ['class' => 'dynamicTable', 'data-index' => $model->id, 'data-lang' => \Yii::$app->language, 'data-is-new' => $model->isNewRecord ? "true" : "false"]);

echo Html::submitButton(\Yii::t('app', 'Save'), ['class' => 'btn btn-success']);

ActiveForm::end();