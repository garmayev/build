<?php

namespace app\modules\api\controllers;

use yii\filters\AccessControl;
use yii\rest\Controller;
use app\models\User;

class UserController extends Controller
{
    public function behaviors()
    {
        return \yii\helpers\ArrayHelper::merge(
            parent::behaviors(),
            [
                'corsFilter' => [ 
                    'class' => \yii\filters\Cors::class,
                    'cors' => [
                        'Origin' => ['*'],
                        'Access-Control-Request-Method' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'HEAD', 'OPTIONS', 'PREFLIGHT'],
                        'Access-Control-Request-Headers' => ['*'],
                        'Access-Control-Allow-Credentials' => null,
                        'Access-Control-Max-Age' => 86400,
                        'Access-Control-Allow-Origin' => ['*'],
                    ],
                ],
                'access' => [
                    'class' => \yii\filters\AccessControl::class,
                    'rules' => [
                        [ 'allow' => true, 'roles' => ['?'], 'actions' => ['check', 'list', 'login', 'register'] ],
                        [ 'allow' => true, 'roles' => ['@'], 'actions' => ['options', 'preflight', 'login', 'register', 'check-username', 'check-email'] ],
                    ],
                ],
                'authenticator' => [
//                    'class' => \yii\filters\auth\HttpBearerAuth::class,
                    'class' => \yii\filters\auth\CompositeAuth::class,
                    'authMethods' => [
                        \yii\filters\auth\HttpBearerAuth::class,
                        \yii\filters\auth\HttpBasicAuth::class,
                        \yii\filters\auth\QueryParamAuth::class,
                    ],
                    'except' => ['OPTIONS', 'PREFLIGHT', 'login', 'register', 'check', 'check-username', 'check-email']
                ]
            ]
        );
    }

    public function actionLogin() {
        $data = json_decode(file_get_contents("php://input"), true);
        $model = User::findOne(['username' => $data['username']]);
        if (empty($data['username']) || empty($data['password'])) {
            return [ "ok" => false, "message" => "Missing Username or Password" ];
        }
        sleep(2);
        if ( $model && $model->validatePassword($data['password']) ) {
            return [ 'ok' => true, 'user' => $model, 'token' => $model->access_token ];
        }
        if ( $model ) {
            return ["ok" => false, 'message' => 'Unknown password'];
        }
        return ["ok" => false, 'message' => 'Unknown username'];
    }

    public function actionOptions() {
        if (\Yii::$app->request->method !== 'OPTIONS') {
            \Yii::$app->response->statusCode = 200;
        }
        \Yii::$app->response->headers->set('Allow', implode(', ', ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'HEAD', 'OPTIONS']));
    }

    public function actionCheck() {
        $data = json_decode(file_get_contents("php://input"), true);
        $model = User::findOne(['access_token' => $data["access_token"]]);
        if (isset($model)) {
            // $user = User::findOne(\Yii::$app->user->identity->id);
            return ['ok' => true, 'user' => $model, 'token' => $model->access_token];
        }
        return ["ok" => false, 'message' => 'Unknown user'];

    }

    public function actionList()
    {
        return User::find()->all();
    }

    public function actionRegister() {
        $data = json_decode(file_get_contents("php://input"), true);
        $model = new User();
        if ($model->load($data, '') && $model->save()) {
            $model->setPasswordHash($data['password']);
            $model->save();
            return ['ok' => true, 'token' => $model->access_token];
        } else {
            return ['ok' => false, 'message' => $model->getErrorSummary(true)];
        }
    }

    public function actionCheckUsername()
    {
        $data = json_decode(file_get_contents("php://input"), true);
        $model = User::find()->where(['username' => $data['username']])->one();
        return ['ok' => empty($model), 'message' => 'This username is already taken'];
    }

    public function actionCheckEmail()
    {
        $data = json_decode(file_get_contents("php://input"), true);
        $model = User::find()->where(['email' => $data['email']])->one();
        return ['ok' => empty($model), 'message' => 'This email is already taken'];
    }
}
