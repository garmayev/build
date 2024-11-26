<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Dimension $model */

$this->title = Yii::t('app', 'Create Dimension');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Dimensions'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="dimension-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
