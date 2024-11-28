<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Coworker $model */

$this->title = Yii::t('app', 'Update Coworker: {name}', [
    'name' => $model->user->name,
]);
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Coworkers'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->user->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('app', 'Update');
?>
<div class="coworker-update">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
