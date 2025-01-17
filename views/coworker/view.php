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
    <nav>
        <div class="nav nav-tabs" id="tabs" role="tabList">
            <a class="nav-link active" href="#account" data-toggle="tab" role="tab" aria-selected="true">
                <?= \Yii::t('app', 'Account') ?>
            </a>
            <a class="nav-link" href="#profile" data-toggle="tab" role="tab" aria-selected="false">
                <?= \Yii::t('app', 'Profile') ?>
            </a>
            <a class="nav-link" href="#properties" data-toggle="tab" role="tab" aria-selected="false">
                <?= \Yii::t('app', 'Properties') ?>
            </a>
        </div>
    </nav>
    <div class="tab-content" id="tabContent">
        <div class="tab-pane fade show active" id="account">
            <?= DetailView::widget([
                'model' => $model->user,
                'attributes' => [
                    [
                        'attribute' => 'username',
                        'label' => \Yii::t('app', 'Login')
                    ], [
                        'attribute' => 'email',
                        'label' => \Yii::t('app', 'Email')
                    ], [
                        'attribute' => 'chat_id',
                        'label' => 'Telegram',
                    ], [
                        'attribute' => 'device_id',
                        'label' => 'Device',
                    ],
                ],
                'options' => [
                    'class' => 'table table-striped',
                ],
            ]) ?>
        </div>
        <div class="tab-pane fade" id="profile">
            <?= DetailView::widget([
                'model' => $model->user->profile,
                'attributes' => [
                    [
                        'attribute' => 'first_name',
                        'label' => \Yii::t('app', 'First Name')
                    ], [
                        'attribute' => 'last_name',
                        'label' => \Yii::t('app', 'Last Name')
                    ], [
                        'attribute' => 'patronymic',
                        'label' => \Yii::t('app', 'Patronymic'),
                    ], [
                        'attribute' => 'birthday',
                        'label' => \Yii::t('app', 'Birthday'),
                    ], [
                        'attribute' => 'biography',
                        'label' => \Yii::t('app', 'Biography'),
                    ],
                ],
                'options' => [
                    'class' => 'table table-striped',
                ],
            ]) ?>
        </div>
        <div class="tab-pane fade" id="properties">
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
                    [
                        'attribute' => 'value',
                        'label' => \Yii::t('app', 'Value')
                    ],
                    [
                        'attribute' => 'dimension_id',
                        'label' => \Yii::t('app', 'Dimensions'),
                        'value' => function (CoworkerProperty $model) {
                            return $model->dimension->title;
                        }
                    ]
                ],
                'tableOptions' => [
                    'class' => 'table table-striped',
                ],
            ]) ?>
        </div>
    </div>
</div>
