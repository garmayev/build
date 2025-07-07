<?php

namespace app\modules\api\controllers;

use yii\filters\AccessControl;
use yii\rest\Controller;
use app\models\User;

class UserController extends \yii\rest\Controller
{
    public $modelClass = User::class;

    public function behaviors()
    {
        return [
            'corsFilter' => [
                'class' => \yii\filters\Cors::class,
                'cors' => [
                    'Origin' => ['*'],
                    'Access-Control-Request-Method' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS', 'PREFLIGHT'],
                    'Access-Control-Request-Headers' => ['*'],
                    'Access-Control-Allow-Credentials' => false,
                    'Access-Control-Max-Age' => 86400,
                    'Access-Control-Allow-Origin' => ['*'],
                ],
            ],
            'access' => [
                'class' => \yii\filters\AccessControl::class,
                'rules' => [
                    // Guests
                    [ 'allow' => true, 'roles' => ['?'], 'actions' => ['login', 'register', 'check-username', 'check-email', 'check', 'set-token', 'info'] ],
                    // Users
                    [ 'allow' => true, 'roles' => ['@'], 'actions' => ['check', 'list', 'login', 'info'] ],
                ],
            ],
            'authenticator' => [
                'class' => \yii\filters\auth\HttpBearerAuth::class,
                'except' => ['OPTIONS', 'PREFLIGHT', 'HEAD', 'login', 'register', 'check-username', 'check-email', 'set-token', 'info'],
            ],
        ];
    }

    protected function verbs()
    {
        return [
            'index' => ['GET', 'OPTIONS'],
            'view' => ['GET', 'OPTIONS'],
            'create' => ['POST', 'OPTIONS'],
            'update' => ['POST', 'PUT', 'OPTIONS'],
            'delete' => ['DELETE', 'OPTIONS'],
            'check' => ['POST', 'OPTIONS'],
            'login' => ['POST', 'OPTIONS'],
            'set-token' => ['POST', 'OPTIONS'],
        ];
    }

    public function beforeAction($action)
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $this->enableCsrfValidation = false;
        return parent::beforeAction($action);
    }

    public function actions()
    {
        $actions = parent::actions();
        $actions['options'] = [
            'class' => \yii\rest\OptionAction::class
        ];
        return $actions;
    }

    public function actionLogin() {
        $data = $_POST;
        $model = User::findOne(['username' => $data['username']]);
        if (empty($data['username']) || empty($data['password'])) {
            return [ "ok" => false, "message" => \Yii::t("app", "Missing Username or Password") ];
        }
        if ( $model && $model->validatePassword($data['password']) ) {
            return [ 'ok' => true, 'user' => $model, 'token' => $model->access_token ];
        }
        if ( $model ) {
            return ["ok" => false, 'message' => \Yii::t("app", 'Missing Username or Password')];
        }
        return ["ok" => false, 'message' => \Yii::t("app", 'Missing Username or Password')];
    }

    public function actionCheck() {
        return ["ok" => !\Yii::$app->user->isGuest, 'model' => \Yii::$app->user->identity];
    }

    public function actionList()
    {
        return User::find()->all();
    }

    public function actionRegister() {
        $data = json_decode(file_get_contents("php://input"), true) ?? $_POST;
        $model = new User();
        $model->username = $data['username'];
        $model->email = $data['email'];
        $model->status = User::STATUS_ACTIVE;
        $model->password_hash = \Yii::$app->security->generatePasswordHash($data['password']);
        $model->auth_key = \Yii::$app->security->generateRandomString();
        $model->access_token = \Yii::$app->security->generateRandomString();
        if ($model->save()) {
            return ['ok' => true, 'user' => $model, 'token' => $model->access_token];
        } else {
            return ['ok' => false, 'message' => $model->getErrorSummary(true)];
        }
    }

    public function actionCheckUsername()
    {
        $data = json_decode(file_get_contents("php://input"), true);
        $model = User::find()->where(['username' => $data['username']])->one();
        if (!empty($model)) {
            return ['ok' => true, 'message' => \Yii::t("app", 'This username is already taken')];
        }
        return ['ok' => false];
    }

    public function actionCheckEmail()
    {
        $data = json_decode(file_get_contents("php://input"), true);
        $model = User::find()->where(['email' => $data['email']])->one();
        if (isset($model)) {
            return ["ok" => true, "message" => \Yii::t("app", "This email is already taken")];
        }
        return ['ok' => false];
    }

    public function actionSetToken($user_id)
    {
//        \Yii::error($user_id);
//        $user = User::findOne($user_id);
        $model = \app\models\Coworker::findOne(['user_id' => $user_id]);
        $data = \Yii::$app->request->post();
        if ($data["token"]) {
            $model->device_id = $data["token"];
            \Yii::error( $data );
            $saved = $model->save();
            return ["ok" => $saved, "message" => !$saved ? $model->errors : ""];
        }
        return ["ok" => false, "message" => \Yii::t("app", "Missing token")];
    }

    public function actionInfo($id)
    {
        $coworker = \app\models\Coworker::findOne($id);
        $model = \app\models\User::findOne(['id' => $coworker->user_id]);
        if (empty($model)) {
            \Yii::$app->user->identity;
        }
        return $model;
//        return ['ok' => true, ''];
    }
}
