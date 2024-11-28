<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Technique $model */

$this->title = Yii::t('app', 'Create Technique');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Techniques'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="technique-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
