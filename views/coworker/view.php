<?php

use app\models\Coworker;
use app\models\CoworkerProperty;
use app\models\Profile;
use yii\data\ArrayDataProvider;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var app\models\User $model */
$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Coworkers'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
$invite_link = "https://t.me/" . \Yii::$app->params["bot_name"] . "?start=" . $model->id;

?>
    <p>
        <?= Html::a(\Yii::t('app', 'Update'), ['update', 'id' => $model->id], ['class' => 'btn btn-success mb-3']) ?>
        <?= Html::button(\Yii::t('app', 'Invite'), [
            'class' => 'btn btn-primary mb-3', 'id' => 'invite', 'data-key' => $model->id, "data-toggle" => "collapse",
            "data-target" => "#collapseExample", "aria-expanded" => "false", "aria-controls" => "collapseExample",
            "type" => "button"]) ?>
    </p>
    <div class="collapse" id="collapseExample">
        <div class="card card-body">
            <?= Html::a($invite_link, $invite_link) ?>
        </div>
    </div>
    <div class="coworker-view row">
        <div class="col-2">
            <?= \app\widgets\UserMenu::widget([
                'items' => [
                    ['label' => Yii::t('app', 'Profile'), 'url' => ['coworker/view', 'id' => $model->id]],
                    ['label' => Yii::t('app', 'Account'), 'url' => ['coworker/account', 'id' => $model->id]],
                ]
            ]) ?>
        </div>
        <div class="col-10">
            <?php
            switch (\Yii::$app->controller->action->id) {
                case "view":
                    echo $this->render("_profile", ["model" => $model->profile]);
                    break;
                case "account":
                    echo $this->render("_account", ["model" => $model]);
                    break;
            }
            ?>
        </div>
    </div>
<?php
