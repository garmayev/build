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
            'title',
            'multiplier',
            'short',
            [
                'class' => ActionColumn::className(),
                'urlCreator' => function ($action, Dimension $model, $key, $index, $column) {
                    return Url::toRoute([$action, 'id' => $model->id]);
                 }
            ],
        ],
    ]); ?>


</div>
