<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Coworker $model */

$name = $model->firstname . ' ' . $model->lastname;
$this->title = Yii::t('app', 'Update Coworker: {name}', [
    'name' => $name,
]);
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Coworkers'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('app', 'Update');
?>
<div class="coworker-update">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
