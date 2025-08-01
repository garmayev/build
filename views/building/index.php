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
            ['class' => 'yii\grid\SerialColumn'],
            [
                'attribute' => 'title',
                'format' => 'raw',
                'headerOptions' => ['class' => 'text-center col-4'],
            ],
            [
                'attribute' => 'location.address',
                'format' => 'raw',
                'headerOptions' => ['class' => 'text-center col-7'],
            ],
            [
                'class' => ActionColumn::className(),
                'headerOptions' => ['class' => 'text-center col-1'],
            ],
        ],
    ]); ?>

</div>
