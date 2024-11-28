<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Building $model */

$this->title = Yii::t('app', 'Create Building');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Buildings'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="building-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
