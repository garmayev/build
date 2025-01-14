<?php

use app\models\Building;
use yii\helpers\Html;
use yii\web\View;
use yii\widgets\ActiveForm;

/**
 * @var View $this
 * @var Building $model
 * @var ActiveForm $form
 */

$this->registerJsFile('//api-maps.yandex.ru/2.1/?apikey=0bb42c7c-0a9c-4df9-956a-20d4e56e2b6b&lang=ru_RU', ['position' => View::POS_HEAD]);
$this->registerJsFile('/js/map.js', ['position' => View::POS_HEAD]);
$position = \yii\helpers\Json::encode($model->location ? [$model->location->latitude, $model->location->longitude] : [51.838814, 107.590673]);

$this->registerJs(<<<JS
$('#show-map').on('click', function () {
    const group = $(this).closest('.form-group');

    group.toggleClass('show');
    if (group.hasClass('show')) {
        window.initMap( 'map', {$position}, 'Building[location]' )
    } else {
        window.destroyMap();
    }
})
JS);

$this->registerCss(<<<CSS
#map {
    display: none;
}
.show #map {
    display: block;
}
CSS);
?>

<div class="building-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'title')->textInput(['maxlength' => true]) ?>

    <div class="form-group">
        <label for="building-location_id"><?= \Yii::t('app', 'Location') ?></label>
        <div class="input-group">
            <input class="form-control" id="building-location_id" />
            <div class="input-group-append">
                <span class="input-group-text" id="show-map"><i class="fas fa-map-marker-alt"></i></span>
            </div>
        </div>
        <div id="map" class="mt-3" style="height: 400px"></div>
    </div>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('app', 'Save'), ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
