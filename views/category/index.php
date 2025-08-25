<?php

use app\models\Category;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;

/** @var yii\web\View $this */
/** @var app\models\search\CategorySearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = Yii::t('app', 'Categories');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="category-index">

    <p>
        <?= Html::a(Yii::t('app', 'Create Category'), ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
//        'filterModel' => $searchModel,
        'summary' => false,
        'tableOptions' => [
            'class' => 'table table-striped'
        ],
        'columns' => [
            [
                'attribute' => 'title',
                'headerOptions' => ['class' => 'text-center col-9 col-md-6'],
                'contentOptions' => ['class' => 'text-center col-9 col-md-6'],
            ],
            [
                'attribute' => 'type',
                'headerOptions' => ['class' => 'text-center col-md-5 hide-on-mobile'],
                'contentOptions' => ['class' => 'text-center col-md-5 hide-on-mobile'],
                'format' => 'html',
                'value' => function (Category $model) {
                    $list = [
                        \app\models\Order::TYPE_COWORKER => \Yii::t('app', 'Coworker'),
                        \app\models\Order::TYPE_MATERIAL => \Yii::t('app', 'Material'),
                        \app\models\Order::TYPE_TECHNIQUE => \Yii::t('app', 'Technique'),
                    ];
                    return Html::a($list[$model->type], ['coworker/index']);
                }
            ],
            [
                'class' => ActionColumn::className(),
                'headerOptions' => ['text-center col-3 col-md-1'],
                'urlCreator' => function ($action, Category $model, $key, $index, $column) {
                    return Url::toRoute([$action, 'id' => $model->id]);
                }
            ],
        ],
    ]); ?>


</div>
