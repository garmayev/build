<?php

use app\models\Order;
use app\models\Report;
use yii\data\ArrayDataProvider;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\web\View;
use yii\widgets\DetailView;

/**
 * @var View $this
 * @var Order $model
 */

\app\assets\GalleryAsset::register($this);

$this->title = \Yii::t('app', 'View Order: {name}', ['name' => "#{$model->id}"]);

$this->params['breadcrumbs'][] = ['label' => \Yii::t('app', 'Orders'), 'url' => ['/order/index']];
$this->params['breadcrumbs'][] = $this->title;

$this->registerCss(<<<CSS
.glide__slide {
    max-height: 75px;
    max-width: 75px;
}
.lg-object.lg-image {
    background-size: cover;
    background: #fff;
}
.lg-thumb-item {
    background-size: cover;
    background: #fff;
}
CSS
);

echo Html::a(\Yii::t('app', 'Notify'), ["order/resend-notify", "id" => $model->id], ["class" => "btn btn-success mb-3 mr-3"]);
echo Html::a(\Yii::t('app', 'Add Report'), ["order/report", "id" => $model->id], ["class" => "btn btn-primary mb-3"]);

echo DetailView::widget([
    'model' => $model,
    'template' => "<tr><th class='col-5 col-md-4'>{label}</th><td class='col-7 col-md-8 text-break'>{value}</td></tr>",
    'attributes' => [
        'building.title',
        [
            'attribute' => 'building.location.address',
            'format' => 'html',
            'value' => function (Order $model) {
                return $model->building->location->link;
            }
        ],
        [
            'attribute' => 'date',
            'value' => function ($model) {
                return Yii::$app->formatter->asDate($model->date, 'php:d m Y');
            }
        ],
        [
            'attribute' => 'status',
            'value' => function (Order $model) {
                return $model->statusTitle;
            }
        ],
        'typeName',
        'comment',
        [
            'attribute' => 'attachments',
            'label' => \Yii::t('app', 'Attachments'),
            'format' => 'raw',
            'value' => function (Order $model) {
                $result = [];
                $images = [];
                foreach ($model->attachments as $attachment) {
                    if (preg_match('/\.jpg|\.png|\.jpeg|\.svg/', $attachment->url, $matches)) {
                        $images[] = $attachment->getLink(false);
                    } else {
                        $result[] = Html::tag('p', Html::a($attachment->url, $attachment->url, ['target' => '_blank']));
                    }
                }
<<<<<<< HEAD
                if ($result) {
                    return implode(", ", $result);
                }
                return null;
=======
                return implode("", $result).Html::tag('div', implode('', $images), ['class' => 'light-gallery']);
>>>>>>> 7d2b156 (Fixes)
            }
        ],
    ],
    'options' => [
        'class' => 'table table-striped'
    ]
]);
?>
    <div class="row">
        <div class="col-6">
            <h4><?= \Yii::t('app', 'Requirements') ?></h4>
            <?php
            echo GridView::widget([
                'dataProvider' => new ArrayDataProvider([
                    'allModels' => $model->requirements
                ]),
                'summary' => false,
                'columns' => [
                    [
                        'attribute' => 'category.title',
                        'headerOptions' => ['class' => 'col-md-2 col-2'],
                    ],
                    [
                        'attribute' => 'count',
                        'headerOptions' => ['class' => 'col-md-2 col-2'],
                    ],
                    [
                        'attribute' => 'property.title',
                        'headerOptions' => ['class' => 'col-md-2 col-2'],
                    ],
                    [
                        'attribute' => 'type',
                        'headerOptions' => ['class' => 'col-md-2 col-2'],
                        'value' => function (\app\models\Requirement $model) {
                            return \Yii::t('app', $model->type);
                        }
                    ],
                    [
                        'attribute' => 'value',
                        'headerOptions' => ['class' => 'col-md-2 col-2'],
                    ],
                    [
                        'attribute' => 'dimension.title',
                        'headerOptions' => ['class' => 'col-md-2 col-2'],
                    ]
                ],
                'tableOptions' => [
                    'class' => 'table table-striped'
                ]
            ]);
            ?>
        </div>
        <div class="col-6">
            <h4><?= \Yii::t('app', 'Coworkers') ?></h4>
            <?php
            echo GridView::widget([
                'dataProvider' => new ArrayDataProvider([
                    'allModels' => $model->coworkers
                ]),
                'summary' => false,
                'columns' => [
                    [
                        'attribute' => 'name',
                        'label' => \Yii::t('app', 'Coworkers'),
                        'headerOptions' => ['class' => 'text-center col-md-3 col-6'],
                        'contentOptions' => ['class' => 'text-center col-md-3 col-6'],
                        'value' => function (\app\models\User $model) {
                            return "{$model->profile->family} {$model->profile->name} {$model->profile->surname}";
                        }
                    ], [
                        'attribute' => 'coworkerProperties',
                        'label' => \Yii::t('app', 'Properties'),
                        'format' => 'raw',
                        'headerOptions' => ['class' => 'text-center col-md-9 col-6'],
                        'contentOptions' => ['class' => 'text-center col-md-9 col-6'],
                        'value' => function (app\models\User $model) {
                            $result = "";
                            foreach ($model->userProperties as $userProperty) {
//                        $type = \Yii::t('app', $userProperty->type);
                                $result .= Html::tag("p", "{$userProperty->property->title} {$userProperty->value} {$userProperty->dimension->title}");
                            }
                            return $result;
                        }
                    ],
                ],
                'tableOptions' => [
                    'class' => 'table table-striped'
                ]
            ]);
            ?>
        </div>
    </div>
    <div class="row">
        <h4><?= \Yii::t('app', 'Reports') ?></h4>
        <?= GridView::widget([
            'dataProvider' => new ArrayDataProvider([
                'allModels' => $model->reports,
            ]),
            'summary' => false,
            'tableOptions' => [
                'class' => 'table table-striped'
            ],
            'columns' => [
                [
                    'attribute' => 'created_at',
                    'format' => 'datetime',
                    'label' => \Yii::t('app', 'Created At'),
                    'headerOptions' => ['class' => 'col-md-2 col-2']
                ],
                [
                    'attribute' => 'comment',
                    'format' => 'html',
                    'label' => \Yii::t('app', 'Comment'),
                    'headerOptions' => ['class' => 'col-md-2 col-2'],
                ],
                [
                    'attribute' => 'attachments',
                    'label' => \Yii::t('app', 'Attachments'),
                    'format' => 'raw',
                    'headerOptions' => ['class' => 'col-md-7 col-7'],
                    'value' => function (Report $model) {
                        $result = "<div class='light-gallery'>";
                        $images = [];
                        foreach ($model->attachments as $attachment) {
                            $images[] = $attachment->getLink(false);
                        }
                        $result .= implode("\n", $images) . "</div>";
                        if (count($images)) {
                            return $result;
                        } else {
                            return null;
                        }
                    }
                ],
                [
                    'class' => \yii\grid\ActionColumn::class,
                    'headerOptions' => ['class' => 'col-md-1 col-1'],
                    'template' => '{delete}',
                    'buttons' => [
                        'delete' => function ($url, Report $model) {
                            return Html::a("<span class='fas fa-trash'></span>", ['order/delete-report', 'id' => $model->id]);
                        }
                    ]
                ]
            ]
        ]) ?>
    </div>
<?php
$this->registerJs(<<<JS
const galleries = document.getElementsByClassName('light-gallery')

Array.from(galleries).forEach(gallery => {
    lightGallery(gallery, {
        plugins: [lgZoom, lgThumbnail],
        licenseKey: 'your_license_key',
        speed: 500,
    });
})
JS
);

$this->registerCss(<<<CSS
.image-container {
    margin-right: 10px;
}
CSS);