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

$this->registerJsFile('//api-maps.yandex.ru/2.1/?apikey=0bb42c7c-0a9c-4df9-956a-20d4e56e2b6b&suggest_apikey=589c9c6f-9d2f-4233-9eb2-789dbc720a6c&lang=ru_RU', ['position' => View::POS_HEAD]);
$this->registerJsFile('/js/map.js', ['position' => View::POS_HEAD]);
?>

<div class="building-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'title')->textInput(['maxlength' => true]) ?>

    <div class="form-group">
        <label for="building-location_id"><?= \Yii::t('app', 'Location') ?></label>
        <div class="input-group">
            <input class="form-control" name="Building[location][address]" id="building-address" value="<?= $model->location ? $model->location->address : '' ?>" />
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
<?php
$location = $model->location ? $model->location : ["latitude" => 51.838814, "longitude" => 107.590673, "address" => ""];
$this->registerJsVar('position', $location);
$this->registerJs(<<<JS
console.log(position)
$(() => {
    $('#show-map').on('click', function () {
        const group = $(this).closest('.form-group');
        group.toggleClass('show');
    })

    ymaps.ready(init)
    
    function init() {
        const map = new Map( 'map', position, 'Building[location]', 'building-address' )
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
