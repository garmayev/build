<?php

use app\models\CategoryProperty;
use yii\data\ArrayDataProvider;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\widgets\DetailView;
use yii\grid\GridView;

/** @var yii\web\View $this */
/** @var app\models\Category $model */

$this->title = $model->title;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Categories'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="category-view">

    <p>
        <?= Html::a(Yii::t('app', 'Update'), ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a(Yii::t('app', 'Delete'), ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => Yii::t('app', 'Are you sure you want to delete this item?'),
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'title',
            'type',
            'parent_id'
        ],
    ]) ?>

    <?= GridView::widget([
        'dataProvider' => new ArrayDataProvider([
            'allModels' => $model->categoryProperties,
        ]),
        'summary' => false,
        'tableOptions' => [
            'class' => 'table table-striped'
        ],
        'columns' => [
            [
                'attribute' => 'property_id',
                'value' => function (CategoryProperty $categoryProperty) {
                    return $categoryProperty->property->title;
                }
            ],
            [
                'attribute' => 'dimension_id',
                'value' => function (CategoryProperty $categoryProperty) {
                    return implode(', ', ArrayHelper::map( $categoryProperty->property->dimensions, 'id', 'title' ));
                }
            ],
        ]
    ]) ?>

</div>
