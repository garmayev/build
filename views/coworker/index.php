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
.table td {
    display: table-cell;
    vertical-align: middle;
}
CSS
);

$actionButtons = [
    [
        'label' => 'Profile',
        'url' => ['profile'],
    ], [
        'label' => 'View',
        'url' => ['view'],
    ], [
        'label' => 'Invite',
        'url' => ['invite'],
    ], [
        'label' => 'Delete',
        'url' => ['delete'],
        'linkOptions' => [
            'data-method' => 'post',
            'class' => 'dropdown-item'
        ],
    ],
];
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
                'class' => 'yii\grid\SerialColumn',
            ],
            'firstname',
            'lastname',
            'email:email',
            'phone',
            [
                'attribute' => 'category_id',
                'label' => \Yii::t('app', 'Category'),
                'value' => function ($model) {
                    return $model->category ? $model->category->title : "";
                }
            ],
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
                'attribute' => 'priority',
                'label' => \Yii::t('app', 'Priority'),
                'format' => 'raw',
                'value' => function (Coworker $model) {
                    $list = [
                        Coworker::PRIORITY_LOW => \Yii::t('app', 'Priority low'),
                        Coworker::PRIORITY_NORMAL => \Yii::t('app', 'Priority normal'),
                        Coworker::PRIORITY_HIGH => \Yii::t('app', 'Priority high'),
                    ];
                    return $list[$model->priority];
                }
            ],
            [
                'label' => \Yii::t('app', 'Contact'),
                'format' => 'raw',
                'value' => function (Coworker $model) {
                    $result = $model->chat_id ? "<i class='fab fa-telegram'></i>" : "";
                    $result .= $model->device_id ? "<i class='fas fa-mobile'></i>" : "";
                    return Html::tag('div', $result, ['style' => 'display: flex; justify-content: space-around; align-item: center; width: 60px;']) ?? Html::tag('i', \Yii::t('yii', 'not-set'), ['class' => 'not-set']);
                }
            ],
            [
                'class' => \microinginer\dropDownActionColumn\DropDownActionColumn::className(),
                'items' => $actionButtons
            ],
        ],
    ]); ?>


</div>
