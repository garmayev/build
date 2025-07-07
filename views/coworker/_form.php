<?php

use app\models\Category;
use app\models\Coworker;
use kartik\depdrop\DepDrop;
use kartik\select2\Select2;
use yii\bootstrap4\Modal;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\web\JsExpression;
use yii\web\View;
use yii\widgets\ActiveForm;

/**
 * @var View $this
 * @var Coworker $model
 * @var ActiveForm $form
 */

$this->registerCssFile("https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css");
$this->registerJsFile("https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js", [
    'depends' => \yii\web\JqueryAsset::class
]);

echo Html::tag('div', "", ['id' => 'coworker-form', 'class' => ['container-fluid', 'my-3'], 'data-index' => $model->id, 'data-lang' => 'ru', ]);