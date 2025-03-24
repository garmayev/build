<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\User $model */
/** @var app\models\forms\UserRegisterForm $registerForm */

$this->title = Yii::t('app', 'Create Coworker');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Coworkers'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$registerForm = new UserRegisterForm($model);
?>
<div class="coworker-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
