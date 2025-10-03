<?php

use yii\bootstrap4\Nav;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\User $model */
$this->title = $model->fullName;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Coworkers'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
$invite_link = "https://t.me/" . \Yii::$app->params["bot_name"] . "?start=" . $model->id;
$this->registerJsVar("token", \Yii::$app->user->identity->access_token);
?>
    <p>
        <?= Html::a(\Yii::t('app', 'Update'), ['update', 'id' => $model->id], ['class' => 'btn btn-success']) ?>
    </p>
    <p>&nbsp;</p>
    <div id="coworker-view" data-index="<?= $model->id ?>" data-lang="<?= \Yii::$app->language ?>"
         data-bot-name="<?= \Yii::$app->params["bot_name"] ?>">
    </div>
<?php
