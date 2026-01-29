<?php

use yii\bootstrap4\Nav;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Coworker $model */
$this->title = $model->fullName;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Coworkers'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
$invite_link = "https://t.me/" . \Yii::$app->params["bot_name"] . "?start=" . $model->id;
$this->registerJsVar("token", \Yii::$app->user->identity->access_token);
?>
    <p>
        <?= Html::a(\Yii::t('app', 'Update'), ['update', 'id' => $model->id], ['class' => 'btn btn-success']) ?>
    </p>
    <?= $this->render('_profile', [
            'model' => $model->profile,
    ]) ?>
    <div class="row">
        <div class="col-6">
            <h4><?= \Yii::t('app', 'Account') ?></h4>
            <?= $this->render('_account', [
                'model' => $model
            ]) ?>
        </div>
        <div class="col-6">
            <h4><?= \Yii::t('app', 'Orders'); ?></h4>
            <?= \yii\grid\GridView::widget([
                'dataProvider' => new \yii\data\ActiveDataProvider([
                    'query' => $model->getOrders()
                ]),
                'summary' => false,
                'tableOptions' => ['class' => 'table table-striped'],
                'showHeader' => false,
                'columns' => [
                    [
                        'attribute' => 'id',
                        'format' => 'raw',
                        'value' => function (\app\models\Order $model) {
                            return Html::a("#{$model->id}", ['/order/view', 'id' => $model->id]);
                        }
                    ],
                    'start_datetime:date',
                    [
                        'attribute' => 'finish_datetime',
                        'format' => 'date',
                        'value' => function (\app\models\Order $model) {
                            if ( $model->mode !== \app\models\Order::MODE_SINGLE_FIXED ) {
                                return $model->finish_datetime;
                            }
                            return null;
                        }
                    ],
                    [
                        'attribute' => 'price',
                        'format' => 'currency',
                        'value' => function (\app\models\Order $model) {
                            if ($model->mode !== \app\models\Order::MODE_LONG_DAILY) {
                                return $model->price;
                            }
                            return null;
                        }
                    ],
                ]
            ]) ?>
        </div>
    </div>
<?php
