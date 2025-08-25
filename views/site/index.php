<?php

/** @var yii\web\View $this */

$this->title = \Yii::$app->name;
?>
<div class="site-index">
    <?php
    echo \yii\helpers\Html::beginTag('div', ['class' => 'row']);
    if (\Yii::$app->user->can('director')) {
        $buildingsCount = \app\models\Building::find()->where(['user_id' => \Yii::$app->user->id])->count();
        echo \yii\helpers\Html::tag('div', \hail812\adminlte\widgets\SmallBox::widget([
            'title' => \Yii::t('app', 'Buildings'),
            'text' => \Yii::t('app', 'Buildings total count') . ": " . $buildingsCount,
            'icon' => 'far fa-building',
            'linkText' => \Yii::t('app', 'See all buildings'),
            'linkUrl' => ['building/index']
        ]), ['class' => 'col-md-3 col-sm-6']);
    }
    if (!\Yii::$app->user->can('admin')) {
        $model = \Yii::$app->user->identity;
        if (empty($model->profile->chat_id)) {
            echo \yii\helpers\Html::tag('div', \hail812\adminlte\widgets\SmallBox::widget([
                'title' => \Yii::t('app', 'Telegram Bot'),
                'text' => \Yii::t('app', 'Telegram Bot'),
                'icon' => 'fab fa-telegram-plane',
                'theme' => 'primary',
                'linkText' => \Yii::t('app', 'Telegram Bot'),
                'linkUrl' => ['telegram-bot'],
            ]), ['class' => 'col-md-3 col-sm-6']);

        }
        if (empty($model->profile->device_id)) {
            echo \yii\helpers\Html::tag('div', \hail812\adminlte\widgets\SmallBox::widget([
                'title' => \Yii::t('app', 'Android App'),
                'text' => \Yii::t('app', 'Android App'),
                'icon' => 'fab fa-android',
                'theme' => 'success',
                'linkText' => \Yii::t('app', 'Android App'),
                'linkUrl' => ['android-app']
            ]), ['class' => 'col-md-3 col-sm-6']);
        }
    }
    echo \yii\helpers\Html::endTag('div');
    ?>
</div>
