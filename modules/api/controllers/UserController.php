<?php

namespace app\modules\api\controllers;

use app\models\forms\UserRegisterForm;
use app\models\Profile;
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
                    'Origin' => ['http://localhost:3000', 'http://build.local', 'https://build.amgcompany.ru'],
                    'Access-Control-Request-Method' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS', 'PREFLIGHT'],
                    'Access-Control-Request-Headers' => ['*'],
                    'Access-Control-Allow-Credentials' => true,
                    'Access-Control-Max-Age' => 86400,
                    'Access-Control-Allow-Origin' => ['*'],
                ],
            ],
            'access' => [
                'class' => \yii\filters\AccessControl::class,
                'rules' => [
                    // Guests
                    ['allow' => true, 'roles' => ['?'], 'actions' => [
                        'login',
                        'register',
                        'check-username',
                        'check-email',
                        'check',
                        'set-token',
                        'info',
                        'change-password',
                        'check-password',
                        'update-account',
                        'update-profile',
                        'get-roles',
                        'create-profile',
                        'create-account',
                    ]],
                    // Users
                    ['allow' => true, 'roles' => ['@'], 'actions' => ['check', 'list', 'login', 'create-profile', 'create-account', 'update-account', 'update-profile']],
                ],
            ],
            'authenticator' => [
                'class' => \yii\filters\auth\HttpBearerAuth::class,
                'except' => ['OPTIONS', 'PREFLIGHT', 'HEAD', 'login', 'register', 'check-username', 'check-email', 'set-token', 'create-profile', 'create-account', 'update-profile', 'update-account'],
            ],
        ];
    }

    protected function verbs()
    {
        return [
            'index' => ['GET', 'OPTIONS'],
            'view' => ['GET', 'OPTIONS'],
            'info' => ['GET', 'OPTIONS'],
            'create' => ['POST', 'OPTIONS'],
            'update' => ['POST', 'PUT', 'OPTIONS'],
            'delete' => ['DELETE', 'OPTIONS'],
            'check' => ['POST', 'OPTIONS'],
            'login' => ['POST', 'OPTIONS'],
            'set-token' => ['POST', 'OPTIONS'],
            'get-roles' => ['GET', 'OPTIONS'],
            'change-password' => ['POST', 'OPTIONS'],
            'check-password' => ['POST', 'OPTIONS'],
            'update-account' => ['POST', 'OPTIONS'],
            'update-profile' => ['POST', 'OPTIONS'],
            'create-account' => ['POST', 'OPTIONS'],
            'create-profile' => ['POST', 'OPTIONS'],
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

    public function actionLogin()
    {
        $data = $_POST;
        $model = User::findOne(['username' => $data['username']]);
        if (empty($data['username']) || empty($data['password'])) {
            return ["ok" => false, "message" => \Yii::t("app", "Missing Username or Password")];
        }
        if ($model && $model->validatePassword($data['password'])) {
            return ['ok' => true, 'user' => $model, 'token' => $model->access_token];
        }
        if ($model) {
            return ["ok" => false, 'message' => \Yii::t("app", 'Missing Username or Password')];
        }
        return ["ok" => false, 'message' => \Yii::t("app", 'Missing Username or Password')];
    }

    public function actionCheck()
    {
        return ["ok" => !\Yii::$app->user->isGuest, 'model' => User::findOne(\Yii::$app->user->getId())];
    }

    public function actionList()
    {
        return User::find()->all();
    }

    public function actionRegister()
    {
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
        $model = \app\models\Profile::findOne(['id' => $user_id]);
        $data = \Yii::$app->request->post();
        if ($data["token"]) {
            $model->device_id = $data["token"];
//            \Yii::error($data);
            $saved = $model->save();
            return ["ok" => $saved, "message" => !$saved ? $model->errors : ""];
        }
        return ["ok" => false, "message" => \Yii::t("app", "Missing token")];
    }

    public function actionInfo($id = null)
    {
        $user = User::find()->joinWith('profile')->where(['user.id' => $id])->one();
        if (empty($user)) {
            return new User();
        }
        return $user;
    }

    public function actionChangePassword($id)
    {
        $model = User::findOne(['id' => $id]);
        $post = \Yii::$app->request->post();
        if ($model->validatePassword($post['old_password'])) {
            $model->password_hash = \Yii::$app->security->generatePasswordHash($post['new_password']);
            if ($model->save()) {
                return ["ok" => true, "message" => \Yii::t("app", "Password changed successfully")];
            }
            return ["ok" => false, "message" => $model->getErrorSummary(true)];
        } else {
            return ["ok" => false, "message" => \Yii::t("app", "Wrong password")];
        }
    }

    public function actionCheckPassword($id)
    {
        $model = User::findOne($id);
        $post = \Yii::$app->request->post();
        if ($model->validatePassword($post['password'])) {
            return ["ok" => true, "message" => \Yii::t("app", "Password checked successfully")];
        }
        return ["ok" => false, "message" => \Yii::t("app", "Wrong password")];
    }

    public function actionUpdateAccount($id)
    {
        $model = User::findOne($id);
        $post = \Yii::$app->request->post();
        if ($model->load(["User" => $post]) && $model->save()) {
            return ["ok" => true, "message" => \Yii::t("app", "Account updated successfully")];
        }
        return ["ok" => false, "message" => $model->getErrorSummary(true)];
    }

    public function actionCreateAccount()
    {
        $model = new User();
        if ($model->loadApi(array_merge(\Yii::$app->request->post(), ['referrer_id' => \Yii::$app->user->getId()]))) {
            return ["ok" => true, "message" => \Yii::t("app", "Account created successfully"), 'model' => $model];
        }
        return ["ok" => false, "message" => $model->errors];
    }

    public function actionUpdateProfile($id)
    {
        $model = Profile::findOne($id);
        $post = \Yii::$app->request->post();
        if ($model->load(["Profile" => $post]) && $model->save()) {
            return ["ok" => true, "message" => \Yii::t("app", "Profile updated successfully")];
        }
        return ["ok" => false, "message" => $model->getErrorSummary(true)];
    }

    public function actionCreateProfile()
    {
        $profile = new Profile();
        if ($profile->load(\Yii::$app->request->post()) && $profile->save()) {
            return ["ok" => true, "message" => \Yii::t("app", "Profile created successfully"), "model" => $profile];
        }
        return ["ok" => false, "message" => $profile->errors];
    }

    public function actionGetRoles()
    {
        $roles = \Yii::$app->authManager->getRoles();
        if (\Yii::$app->user->can('admin')) {
            return [
                'admin' => ['name' => 'admin'],
                'director' => ['name' => 'director'],
                'employee' => ['name' => 'employee'],
            ];
        } else if (\Yii::$app->user->can('director')) {
            return [
                'director' => ['name' => 'director'],
                'employee' => ['name' => 'employee'],
            ];
        } else {
            return ['employee' => ['name' => 'employee']];
        }
    }
}
