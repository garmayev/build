<?php

use app\models\Coworker;
use app\models\CoworkerProperty;
use yii\data\ArrayDataProvider;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var app\models\Coworker $model */
//var_dump($model->firstname);
$this->title = "{$model->firstname} {$model->lastname}";
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Coworkers'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="coworker-view">
    <?= Html::a(\Yii::t('app', 'Update'), ['update', 'id' => $model->id], ['class' => 'btn btn-success mb-3']) ?>
    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            [
                'attribute' => 'firstname',
                'label' => \Yii::t('app', 'First Name')
            ], [
                'attribute' => 'lastname',
                'label' => \Yii::t('app', 'Last Name')
            ], [
                'attribute' => 'email',
                'label' => \Yii::t('app', 'Email'),
            ], [
                'attribute' => 'phone',
                'label' => \Yii::t('app', 'Phone'),
            ], [
                'attribute' => 'priority',
                'label' => \Yii::t('app', 'Priority'),
                'value' => function (Coworker $model) {
                    switch ($model->priority) {
                        case Coworker::PRIORITY_LOW:
                            return Yii::t('app', 'Priority low');
                        case Coworker::PRIORITY_NORMAL:
                            return Yii::t('app', 'Priority normal');
                        case Coworker::PRIORITY_HIGH:
                            return Yii::t('app', 'Priority high');
                    }
                }
            ], [
                'attribute' => 'attachments',
                'label' => \Yii::t('app', 'Attachments'),
                'format' => 'raw',
                'value' => function (Coworker $model) {
                    $result = "";
                    foreach ($model->attachments as $attachment) {
                        $result .= Html::a($attachment->url, $attachment->url, ['target' => '_blank']);
                    }
                    return $result;
                }
            ]
        ],
        'options' => [
            'class' => 'table table-striped',
        ],
    ]) ?>

    <?= GridView::widget([
        'dataProvider' => new ArrayDataProvider([
            'allModels' => $model->coworkerProperties,
        ]),
        'summary' => false,
        'columns' => [
            [
                'attribute' => 'property.title',
            ],
            'value',
            [
                'attribute' => 'dimension.title',
            ]
        ],
        'tableOptions' => [
            'class' => 'table table-striped',
        ]
    ]) ?>
</div>
