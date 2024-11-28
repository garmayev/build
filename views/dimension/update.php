<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Dimension $model */

$this->title = Yii::t('app', 'Update Dimension: {name}', [
    'name' => $model->title,
]);
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Dimensions'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->title, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('app', 'Update');
?>
<div class="dimension-update">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
