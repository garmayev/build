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
$this->registerJsFile('//api-maps.yandex.ru/2.1/?apikey=0bb42c7c-0a9c-4df9-956a-20d4e56e2b6b&suggest_apikey=589c9c6f-9d2f-4233-9eb2-789dbc720a6c&lang=ru_RU', ['position' => View::POS_HEAD]);
$this->registerJsFile('/js/map.js');
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
        'attributes' => [
            'title',
            'location.address',
            [
                'attribute' => 'location',
                'label' => \Yii::t('app', 'Map'),
                'format' => 'raw',
                'value' => function (Building $model) {
                    return Html::tag("div", "", ["id" => "map", 'data' => $model->location->attributes, 'class' => 'col-6', 'style' => 'height: 400px']);
                }
            ],
            'radius'
        ],
    ]) ?>
</div>
<?php
$this->registerJs(<<<JS
$(() => {
    const mapContainer = document.getElementById("map");
    ymaps.ready(init)
    
    function init() {
        console.log(mapContainer.getAttribute('data-latitude'));
        console.log(mapContainer.getAttribute('data-longitude'));
        const map = new Map( "map", {latitude: mapContainer.getAttribute('data-latitude'), longitude: mapContainer.getAttribute('data-longitude')} )
//        map.address = mapContainer.getAttribute('data-address');
    }
//    const map = new Map(mapContainer, [mapContainer.getAttribute('data-latitude'), mapContainer.getAttribute('data-longitude')], '');
});
JS);