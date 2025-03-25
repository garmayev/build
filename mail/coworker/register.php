<?php
use yii\helpers\Html;

/**
 * @var $this \yii\web\View view component instance
 * @var $message \yii\mail\MessageInterface the message being composed
 * @var $user \app\models\forms\UserRegisterForm
 */
?>
<h3 style="text-align: center">Your username:</h3>
<h5 style="text-align: center"><?= $user->username ?></h5>
<h3 style="text-align: center">Your password:</h3>
<h5 style="text-align: center"><?= $user->new_password ?></h5>