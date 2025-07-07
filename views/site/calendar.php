<?php

/**
 * @var \yii\web\View $this
 */

use yii\web\View;

$this->title = \Yii::t('app', 'Calendar');
$this->registerJs('window.token = "'.\Yii::$app->user->identity->access_token.'"', View::POS_HEAD);
echo \yii\helpers\Html::tag("div", "", ["class" => "calendar"]);
