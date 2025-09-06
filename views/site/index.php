<?php

/** @var yii\web\View $this */

use hail812\adminlte\widgets\SmallBox;

$this->title = \Yii::$app->name;
?>
<div class="site-index">
    <?php
    echo \yii\helpers\Html::beginTag('div', ['class' => 'row']);
    $buildingsCount = \app\models\Building::find()->where(['user_id' => \Yii::$app->user->id])->count();
    echo \yii\helpers\Html::tag('div', SmallBox::widget([
        'title' => \Yii::t('app', 'Buildings'),
        'text' => \Yii::t('app', 'Buildings total count') . ": " . $buildingsCount,
        'icon' => 'far fa-building',
        'linkText' => \Yii::t('app', 'See all buildings'),
        'linkUrl' => ['building/index'],
    ]), ['class' => \Yii::$app->user->can('director') || \Yii::$app->user->can('admin') ? 'col-3 d-block' : 'col-3 d-none']);
    echo \yii\helpers\Html::tag('div', SmallBox::widget([
        'title' => \Yii::t('app', 'Orders'),
        'text' => \Yii::t('app', 'Orders count') . ": " . count(\Yii::$app->user->identity->orders),
        'icon' => 'fab fa-first-order',
        'theme' => 'primary',
        'linkText' => \Yii::t('app', 'Telegram Bot'),
        'linkUrl' => ['telegram-bot'],
    ]), ['class' => \Yii::$app->user->can('admin') || \Yii::$app->user->can('director') ? 'col-3 d-block' : 'col-3 d-none']);
    $model = \Yii::$app->user->identity;
    echo \yii\helpers\Html::tag('div', SmallBox::widget([
        'title' => \Yii::t('app', 'Telegram Bot'),
        'text' => \Yii::t('app', 'Telegram Bot'),
        'icon' => 'fab fa-telegram-plane',
        'theme' => 'primary',
        'linkText' => \Yii::t('app', 'Telegram Bot'),
        'linkUrl' => ['telegram-bot'],
    ]), ['class' => \Yii::$app->user->can('employee') ? 'col-3 d-block' : 'col-3 d-none']);
    echo \yii\helpers\Html::tag('div', SmallBox::widget([
        'title' => \Yii::t('app', 'Android App'),
        'text' => \Yii::t('app', 'Android App'),
        'icon' => 'fab fa-android',
        'theme' => 'success',
        'linkText' => \Yii::t('app', 'Android App'),
        'linkUrl' => ['android-app'],
    ]), ['class' => \Yii::$app->user->can('employee') ? 'col-3 d-block' : 'col-3 d-none']);
    $coworkersToday = \app\models\Hours::find()->where(['date' => \Yii::$app->formatter->asDate('now', 'php:yy-m-d')])->count();
    echo \yii\helpers\Html::tag('div', SmallBox::widget([
        'title' => \Yii::t('app', 'Coworkers count'),
        'text' => \Yii::t('app', 'Total count coworkers today') . ": " . $coworkersToday,
        'icon' => 'fas fa-user',
        'theme' => 'secondary',
        'linkText' => \Yii::t('app', 'Calendar'),
        'linkUrl' => ['site/calendar'],
    ]), ['class' => \Yii::$app->user->can('director') || \Yii::$app->user->can('admin') ? 'col-3 d-block' : 'col-3 d-none']);
    echo \yii\helpers\Html::endTag('div');
    ?>
</div>
