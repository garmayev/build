<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\User $model */

$name = $model->fullName;
$this->title = Yii::t('app', 'Update Coworker: {name}', [
    'name' => $name,
]);
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Coworkers'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('app', 'Update');
$this->registerJsVar("token", \Yii::$app->user->identity->access_token);
?>
<div id="coworker-form" class="container-fluid" data-index="<?= $model->id ?>" data-lang="<?= \Yii::$app->language ?>">
</div>
