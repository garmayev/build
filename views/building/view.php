<?php

use app\models\Building;
use yii\helpers\Html;
use yii\web\View;
use yii\web\YiiAsset;
use yii\widgets\DetailView;

/** @var View $this */
/** @var Building $model */

$this->title = $model->title;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Buildings'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
YiiAsset::register($this);
$this->registerJsVar('token', \Yii::$app->user->identity->access_token);
//$this->registerJsFile('//api-maps.yandex.ru/2.1/?apikey=0bb42c7c-0a9c-4df9-956a-20d4e56e2b6b&suggest_apikey=589c9c6f-9d2f-4233-9eb2-789dbc720a6c&lang=ru_RU', ['position' => View::POS_HEAD]);
//$this->registerJsFile('/js/map.js');
// var_dump($model->location->attributes);
?>
    <div class="building-view">

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
            'options' => ['class' => 'table table-striped detail-view'],
            'attributes' => [
                'title',
                'location.address',
                [
                    'attribute' => 'location',
                    'label' => \Yii::t('app', 'Map'),
                    'format' => 'raw',
                    'value' => function (Building $model) {
                        return Html::tag("div", "", [
                            "id" => "yandex-maps-view",
                            'data' => [
                                'index' => $model->id,
                                'lang' => \Yii::$app->language,
                                'api-key' => "0bb42c7c-0a9c-4df9-956a-20d4e56e2b6b",
                                'height' => "400px",
                                'width' => '100%'
                            ],
                            'class' => 'col-12 col-md-6',
                        ]);
                    }
                ],
                'radius'
            ],
        ]) ?>
    </div>
<?php
