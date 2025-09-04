<?php

use app\models\Property;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;

/** @var yii\web\View $this */
/** @var app\models\search\PropertySearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = Yii::t('app', 'Properties');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="property-index">
    <p>
        <?= Html::a(Yii::t('app', 'Create Property'), ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'summary' => false,
        'tableOptions' => [
            'class' => ['table', 'table-striped']
        ],
        'columns' => [
            [
                'attribute' => 'title',
                'headerOptions' => ['class' => 'col-9 col-md-11'],
                'filterOptions' => ['class' => 'col-9 col-md-11'],
                'contentOptions' => ['class' => 'col-9 col-md-11'],
            ],
            [
                'class' => ActionColumn::className(),
                'headerOptions' => ['class' => 'col-3 col-md-1'],
                'urlCreator' => function ($action, Property $model, $key, $index, $column) {
                    return Url::toRoute([$action, 'id' => $model->id]);
                }
            ],
        ],
    ]); ?>


</div>
