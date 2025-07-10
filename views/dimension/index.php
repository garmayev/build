<?php

use app\models\Dimension;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;

/** @var yii\web\View $this */
/** @var app\models\search\DimensionSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = Yii::t('app', 'Dimensions');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="dimension-index">

    <p>
        <?= Html::a(Yii::t('app', 'Create Dimension'), ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'tableOptions' => [
            'class' => ['table', 'table-striped']
        ],
        'summary' => false,
        'columns' => [
            [
                'attribute' => 'title',
                'headerOptions' => ['class' => 'text-center col-8'],
            ],
            [
                'attribute' => 'short',
                'headerOptions' => ['class' => 'text-center col-3'],
            ],
            [
                'class' => ActionColumn::className(),
                'headerOptions' => ['class' => 'col-1'],
                'contentOptions' => ['class' => 'd-flex justify-content-around'],
                'urlCreator' => function ($action, Dimension $model, $key, $index, $column) {
                    return Url::toRoute([$action, 'id' => $model->id]);
                }
            ],
        ],
    ]); ?>


</div>
