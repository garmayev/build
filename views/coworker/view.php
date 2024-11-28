<?php

use app\models\Coworker;
use app\models\CoworkerProperty;
use yii\data\ArrayDataProvider;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var app\models\Coworker $model */

$this->title = $model->user->name;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Coworkers'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="coworker-view">

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
            [
                'attribute' => 'user_id',
                'label' => \Yii::t('app', 'Username'),
                'value' => function (Coworker $model) {
                    return $model->user->name;
                }
            ], [
                'attribute' => 'user.username',
                'label' => \Yii::t('app', 'Login')
            ], [
                'attribute' => 'user.email',
                'label' => \Yii::t('app', 'Email')
            ], [
                'attribute' => 'user.chat_id',
                'label' => 'Telegram',
                'format' => 'raw',
                'value' => function (Coworker $model) {
                    return $model->user->chat_id;
                }
            ], [
                'attribute' => 'user.device_id',
                'label' => 'Device ID',
                'value' => function (Coworker $model) {
                    return $model->user->device_id;
                }
            ], [
                'attribute' => 'category_id',
                'value' => function (Coworker $model) {
                    return $model->category->title;
                }
            ],
        ],
    ]) ?>

    <?= GridView::widget([
        'dataProvider' => new ArrayDataProvider([
            'allModels' => $model->coworkerProperties
        ]),
        'summary' => false,
        'columns' => [
            [
                'attribute' => 'property_id',
                'label' => \Yii::t('app', 'Property'),
                'value' => function (CoworkerProperty $model) {
                    return $model->property->title;
                }
            ],
            'value',
            [
                'attribute' => 'dimension_id',
                'label' => \Yii::t('app', 'Dimension'),
                'value' => function (CoworkerProperty $model) {
                    return $model->dimension->title;
                }
            ]
        ]
    ]) ?>

</div>
