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
        'filterModel' => $searchModel,
        'summary' => false,
        'tableOptions' => [
            'class' => 'table table-striped'
        ],
        'columns' => [
            'title',
            [
                'attribute' => 'type',
                'value' => function (Category $model) {
                    $list = [
                        \app\models\Order::TYPE_COWORKER => \Yii::t('app', 'Coworker'),
                        \app\models\Order::TYPE_MATERIAL => \Yii::t('app', 'Material'),
                        \app\models\Order::TYPE_TECHNIQUE => \Yii::t('app', 'Technique'),
                    ];
                    return $list[$model->type];
                }
            ],
            'parent_id',
            [
                'class' => ActionColumn::className(),
                'urlCreator' => function ($action, Category $model, $key, $index, $column) {
                    return Url::toRoute([$action, 'id' => $model->id]);
                }
            ],
        ],
    ]); ?>


</div>
