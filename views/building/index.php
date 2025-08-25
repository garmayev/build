<?php

use yii\helpers\Html;
use yii\grid\ActionColumn;
use yii\grid\GridView;

/**
 * @var yii\web\View $this
 * @var yii\data\ActiveDataProvider $dataProvider
 */

$this->title = Yii::t('app', 'Buildings');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="building-index">
    <p>
        <?= Html::a(Yii::t('app', 'Create Building'), 'create', ['class' => 'btn btn-success']) ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'tableOptions' => [
            'class' => 'table table-striped'
        ],
        'summary' => false,
        'columns' => [
            [
                'class' => 'yii\grid\SerialColumn',
                'headerOptions' => ['class' => 'text-center col-md-1 col-3'],
                'contentOptions' => ['class' => 'text-center col-md-1 col-3'],
            ],
            [
                'attribute' => 'title',
                'format' => 'raw',
                'headerOptions' => ['class' => 'text-center col-md-4 col-6'],
                'contentOptions' => ['class' => 'text-center col-md-4 col-6'],
            ],
            [
                'attribute' => 'location.address',
                'format' => 'raw',
                'headerOptions' => ['class' => 'text-center col-md-6 col-0 hide-on-mobile'],
                'contentOptions' => ['class' => 'text-center col-md-6 col-0 hide-on-mobile'],
            ],
            [
                'class' => ActionColumn::className(),
                'headerOptions' => ['class' => 'text-center col-md-1 col-3'],
                'contentOptions' => ['class' => 'text-center col-md-1 col-3']
            ],
        ],
    ]); ?>

</div>
