<?php

/** @var yii\web\View $this */

$this->title = \Yii::$app->name;
?>
<div class="site-index">
    <div class="row">
        <div class="col-lg-3 col-6">
            <?php
            if (\Yii::$app->user->can('director')) {
                $buildingsCount = \app\models\Building::find()->where(['user_id' => \Yii::$app->user->id])->count();
                echo \hail812\adminlte\widgets\SmallBox::widget([
                    'title' => \Yii::t('app', 'Buildings'),
                    'text' => \Yii::t('app', 'Buildings total count') . ": " . $buildingsCount,
                    'icon' => 'far fa-building',
                    'linkText' => \Yii::t('app', 'See all buildings'),
                    'linkUrl' => ['building/index']
                ]);
            } ?>
        </div>
    </div>
</div>
