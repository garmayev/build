<?php

use app\models\Coworker;
use app\models\User;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;

/** @var yii\web\View $this */
/** @var yii\data\ArrayDataProvider $dataProvider */

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

$actionButtons = [
    [
        'label' => \Yii::t('app', 'View'),
        'url' => ['view'],
    ], [
        'label' => \Yii::t('app', 'Update'),
        'url' => ['update'],
    ], [
        'label' => \Yii::t('app', 'Invite Mail'),
        'url' => ['invite'],
    ], [
        'label' => \Yii::t('app', 'Telegram Link'),
        'url' => ['telegram-link']
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

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'summary' => false,
        'tableOptions' => [
            'class' => 'table table-striped',
        ],
        'columns' => [
            [
                'class' => 'yii\grid\SerialColumn',
            ],
            [
                'attribute' => 'profile.name',
                'format' => 'raw',
                'value' => function (User $model) {
                    $profileName = ltrim("{$model->profile->family} {$model->profile->name} {$model->profile->surname}");
                    return strlen($profileName) ? $profileName : $model->username;
                }
            ],
            'email:email',
            [
                'attribute' => 'profile.phone',
                'format' => 'raw',
                'value' => function (User $model) {
                    return !empty($model->profile->phone) ? $model->profile->phone : Html::tag('span', \Yii::t('yii', '(not set)'), ['class' => 'not-set']);
                }
            ],
            'profile.birthday:date',
            [
                'format' => 'raw',
                'label' => \Yii::t('yii', 'Social'),
                'value' => function (User $model) {
                    $result = $model->profile->chat_id ? Html::tag('span', "", ['class' => 'fab fa-telegram mx-2']) : '';
                    $result .= $model->profile->device_id ? Html::tag('span', "", ['class' => 'fas fa-mobile mx-2']) : '';
                    return !empty($result) ? $result : Html::tag('span', \Yii::t('yii', '(not set)'), ['class' => 'not-set']);
                }
            ],
            [
                'class' => \microinginer\dropDownActionColumn\DropDownActionColumn::className(),
                'items' => $actionButtons
            ],
        ],
    ]); ?>


</div>
