<?php

use app\models\Filter;
use yii\web\View;

/**
 * @var $this View
 * @var $model Filter
 */

$coworkers = $model->coworkers;

var_dump($coworkers);

//foreach ($coworkers as $key => $value) {

//    var_dump($value);
//    echo "$key => $value->firstname $value->lastname<br>";
//}