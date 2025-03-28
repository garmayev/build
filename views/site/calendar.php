<?php

/**
 * @var \yii\web\View $this
 */

$this->title = \Yii::t('app', 'Calendar');
$this->registerJsFile("/buildCalendar/js/main.3260ff70.js", ['depends' => [\yii\web\JqueryAsset::class]]);
$this->registerJsFile("/buildCalendar/js/453.57e5436f.chunk.js", ['depends' => [\yii\web\JqueryAsset::class]]);
$this->registerCssFile("/buildCalendar/css/main.e1b16440.css");
echo \yii\helpers\Html::tag("div", "", ["class" => "coworker-form"]);
