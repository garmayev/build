<?php

/**
 * @var \yii\web\View $this
 */

$this->title = \Yii::t('app', 'Calendar');
$this->registerJsFile("/js/453.57e5436f.chunk.js", ['depends' => [\yii\web\JqueryAsset::class]]);
$this->registerJsFile("/js/main.21609dde.js", ['depends' => [\yii\web\JqueryAsset::class]]);
$this->registerCssFile("/css/main.2fce8a79.css");
$this->registerJsVar('token', \Yii::$app->user->identity->access_token);
$this->registerCss(<<<CSS
thead td {
    font-weight: 700 !important;
}
td[rowspan="2"] {
    vertical-align: middle;
}
.modal {
    width: 100vw !important;
}
CSS);
echo \yii\helpers\Html::tag("div", "", ["class" => "calendar"]);
