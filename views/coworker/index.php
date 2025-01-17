<?php

use app\models\Coworker;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;

/** @var yii\web\View $this */
/** @var app\models\search\CoworkerSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = Yii::t('app', 'Coworkers');
$this->params['breadcrumbs'][] = $this->title;
$this->registerCss(<<<CSS
.table p {
    padding: 0;
    margin: 0;
}
CSS);
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
                'attribute' => 'user_id',
                'label' => \Yii::t('app', 'Coworker'),
                'format' => 'raw',
                'value' => function (Coworker $model) {
                    return Html::a($model->user->name, ['view', 'id' => $model->id]);
                }
            ],
            'category.title',
            [
                'attribute' => 'coworkerProperties',
                'label' => \Yii::t('app', 'Properties'),
                'format' => 'raw',
                'value' => function (Coworker $model) {
                    $result = "";
                    foreach ($model->coworkerProperties as $coworkerProperty) {
                        $result .= "<p>";
                        if ($coworkerProperty->property) {
                            $result .= $coworkerProperty->property->title;
                        }
                        $result .= " $coworkerProperty->value";
                        if ($coworkerProperty->dimension) {
                            $result .= " {$coworkerProperty->dimension->title}";
                        }
                        $result .= "</p>";
                    }
                    return $result;
                }
            ],
            [
                'class' => ActionColumn::className(),
                'urlCreator' => function ($action, Coworker $model, $key, $index, $column) {
                    return Url::toRoute([$action, 'id' => $model->id]);
                 }
            ],
        ],
    ]); ?>


</div>
