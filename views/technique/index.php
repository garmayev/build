<?php

use app\models\Technique;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;

/** @var yii\web\View $this */
/** @var app\models\search\TechniqueSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = Yii::t('app', 'Techniques');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="technique-index">

    <p>
        <?= Html::a(Yii::t('app', 'Create Technique'), ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            'id',
            'title',
            'coworker.user.name',
            [
                'class' => ActionColumn::className(),
                'urlCreator' => function ($action, Technique $model, $key, $index, $column) {
                    return Url::toRoute([$action, 'id' => $model->id]);
                 }
            ],
        ],
        'tableOptions' => [
            'class' => 'table table-striped'
        ]
    ]); ?>


</div>
