<?php

use app\models\Coworker;
use yii\helpers\Html;
use yii\web\View;
use yii\widgets\ActiveForm;

/**
 * @var View $this
 * @var Coworker $model
 * @var ActiveForm $form
 */

echo Html::tag('div', '', ['id' => 'coworker-form', 'class' => 'container-fluid my-3', 'data-index' => $model->id, 'data-lang' => 'ru']);