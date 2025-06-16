<?php

/**
 * @var \yii\web\View $this
 */

use yii\web\View;

$this->title = \Yii::t('app', 'Calendar');
$this->registerJs('window.token = "tW2zUiaTIWsiy8Shspyy_h9_kTVTJnDn";', View::POS_HEAD);
echo \yii\helpers\Html::tag("div", "", ["class" => "calendar"]);
