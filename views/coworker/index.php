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
        'label' => \Yii::t('app', 'Profile'),
        'url' => ['profile'],
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
            'name',
            'email:email',
            'profile.phone',
            [
                'class' => \microinginer\dropDownActionColumn\DropDownActionColumn::className(),
                'items' => $actionButtons
            ],
        ],
    ]); ?>


</div>
