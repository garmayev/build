<?php

/**
 * @var \yii\web\View $this
 */

$this->title = \Yii::t('app', 'Calendar');
$this->registerJsFile("/js/453.57e5436f.chunk.js", ['depends' => [\yii\web\JqueryAsset::class]]);
$this->registerJsFile("/js/main.0a0359c6.js", ['depends' => [\yii\web\JqueryAsset::class]]);
$this->registerCssFile("/css/main.2fce8a79.css");
$this->registerJsVar('token', \Yii::$app->user->identity->auth_key);
$this->registerCss(<<<CSS
thead td {
    font-weight: 700 !important;
}
td {
    vertical-align: middle !important;
}
.modal {
    width: 100vw !important;
}
CSS);
echo \yii\helpers\Html::tag("div", "", ["class" => "calendar"]);
