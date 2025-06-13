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
$invite_link = "https://t.me/" . \Yii::$app->params["bot_name"] . "?start=" . $model->id;

var_dump( $model->price );
?>
    <div class="coworker-view">
        <p>
            <?= Html::a(\Yii::t('app', 'Update'), ['update', 'id' => $model->id], ['class' => 'btn btn-success mb-3']) ?>
            <?= Html::button(\Yii::t('app', 'Invite'), [
                'class' => 'btn btn-primary mb-3', 'id' => 'invite', 'data-key' => $model->id, "data-toggle" => "collapse",
                "data-target" => "#collapseExample", "aria-expanded" => "false", "aria-controls" => "collapseExample",
                "type" => "button"]) ?>
        </p>
        <div class="collapse" id="collapseExample">
            <div class="card card-body">
                <?= Html::a($invite_link, $invite_link) ?>
            </div>
        </div>
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
                    'format' => 'raw',
                    'value' => function ($model) {
                        return \floor12\phone\PhoneFormatter::a($model->phone, ['class' => 'phone-link']);
                    }
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
                        return '';
                    }
                ],
                "price.price",
                [
                    'attribute' => 'type',
                    'label' => \Yii::t('app', 'Type'),
                    'value' => function (Coworker $model) {
                        switch ($model->type) {
                            case Coworker::TYPE_WORKER: return Yii::t('app', 'Coworker');
                            case Coworker::TYPE_CUSTOMER: return Yii::t('app', 'Customer');
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

        <?php
        if ($model->type == Coworker::TYPE_WORKER) {
            echo GridView::widget([
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
            ]);
        } ?>
    </div>
<?php
$this->registerJsVar("bot_name", \Yii::$app->params['bot_name']);