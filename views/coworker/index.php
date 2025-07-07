<?php

use app\models\Coworker;
use app\models\User;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;

/**
 * @var yii\web\View $this
 * @var yii\data\ArrayDataProvider $dataProvider
 * @var \app\models\search\CoworkerSearch $searchModel
 */

$this->title = Yii::t('app', 'Coworkers');
$this->params['breadcrumbs'][] = $this->title;
$this->registerCss(<<<CSS
.table p {
    padding: 0;
    margin: 0;
}
.table td {
    display: table-cell;
    vertical-align: middle;
}
CSS
);
$this->registerJs(<<<JS
$('.masked-input').mask('+7 (999) 999-99-99');
JS
);
$actionButtons = [
    [
        'label' => \Yii::t('app', 'View'),
        'url' => ['view'],
    ], [
        'label' => \Yii::t('app', 'Update'),
        'url' => ['update'],
    ], [
        'label' => \Yii::t('app', 'Delete'),
        'url' => ['delete'],
        'linkOptions' => [
            'data-method' => 'post',
            'class' => 'dropdown-item'
        ],
    ],
];
?>
<div class="coworker-index">
    <p>
        <?= Html::a(Yii::t('app', 'Create Coworker'), ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'summary' => false,
        'tableOptions' => [
            'class' => 'table table-striped',
        ],
        'filterModel' => $searchModel,
        'columns' => [
            [
                'class' => 'yii\grid\SerialColumn',
                'headerOptions' => ['width' => '3%'],
            ],
            [
                'attribute' => 'name',
                'format' => 'raw',
                'label' => Yii::t('app', 'Name'),
                'headerOptions' => ['class' => 'col-2'],
                'filterInputOptions' => [
                    'class' => 'form-control',

                ],
                'value' => function (User $model) {
                    $profileName = ltrim("{$model->profile->family} {$model->profile->name} {$model->profile->surname}");
                    return strlen($profileName) ? $profileName : $model->username;
                }
            ],
            [
                'attribute' => 'email',
                'format' => 'email',
                'label' => Yii::t('app', 'Email'),
                'headerOptions' => ['class' => 'col-2'],
            ],
            [
                'attribute' => 'phone',
                'format' => 'raw',
                'label' => Yii::t('app', 'Phone'),
                'headerOptions' => ['class' => 'col-2'],
                'filterInputOptions' => ['class' => 'form-control masked-input'],
                'value' => function (User $model) {
                    return !empty($model->profile->phone) ? $model->profile->phone : Html::tag('span', \Yii::t('yii', '(not set)'), ['class' => 'not-set']);
                }
            ],
            [
                'attribute' => 'birthday',
                'format' => 'raw',
                'label' => Yii::t('app', 'Birthday'),
                'headerOptions' => ['class' => 'col-2'],
                'filterInputOptions' => ['type' => 'date', 'class' => 'form-control'],
                'value' => function (User $model) {
                    return Yii::$app->formatter->asDate($model->profile->birthday);
                }
            ],
            [
                'format' => 'raw',
                'label' => \Yii::t('app', 'Devices'),
                'headerOptions' => ['class' => 'col-1'],
                'value' => function (User $model) {
                    $result = $model->profile->chat_id ? Html::tag('span', "", ['class' => 'fab fa-telegram mx-2']) : '';
                    $result .= $model->profile->device_id ? Html::tag('span', "", ['class' => 'fas fa-mobile mx-2']) : '';
                    return !empty($result) ? $result : Html::tag('span', \Yii::t('yii', '(not set)'), ['class' => 'not-set']);
                }
            ],
            [
                'attribute' => 'userProperties',
                'format' => 'raw',
                'label' => Yii::t('app', 'Properties'),
                'headerOptions' => ['class' => 'col-2'],
                'value' => function (User $model) {
                    $result = "";
                    foreach ($model->userProperties as $userProperty) {
                        $result .= "<p>{$userProperty->category->title} {$userProperty->property->title} {$userProperty->value} {$userProperty->dimension->title}</p>";
                    }
                    return strlen($result) ? $result : "<span class='not-set'>" . \Yii::t('yii', '(not set)') . "</span>";
                }
            ],
            [
                'class' => \microinginer\dropDownActionColumn\DropDownActionColumn::className(),
                'items' => $actionButtons
            ],
        ],
    ]); ?>


</div>
