<?php
namespace app\modules\api\controllers;

class ExampleController extends \yii\rest\Controller 
{
    public $serializer = [
        'class' => 'yii\rest\Serializer',
        'collectionEnvelope' => 'data',
    ];

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
                    [ 'allow' => true, 'roles' => ['@'], 'actions' => [] ],
                    // Users
                    [ 'allow' => true, 'roles' => ['?'], 'actions' => ['index', 'view', 'update', 'create', 'delete', 'register', 'check-chat-id'] ],
                ],
            ],
            'authenticator' => [
                'class' => \yii\filters\auth\HttpBearerAuth::class,
                'except' => ['OPTIONS', 'PREFLIGHT', 'HEAD', 'register', 'check-chat-id']
            ],
        ];
    }

    protected function verbs()
    {
        return [
            'view' => ['GET', 'OPTIONS'],
            'status' => ['GET', 'OPTIONS'],
            'create' => ['POST', 'OPTIONS'],
            'update' => ['POST', 'PUT', 'OPTIONS'],
            'delete' => ['DELETE', 'OPTIONS'],
            'check-chat-id' => ['GET', 'OPTIONS'],
            'register' => ['POST', 'OPTIONS'],
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

    public function actionRegister()
    {
        $raw = \Yii::$app->request->post();
        $phone = preg_replace('/[+]/', '', $raw['phone_number']);
        $model = \app\models\User::findByPhone($phone);
//        \Yii::error($model->attributes);
        if (empty($model)) {
            $user = new User([
                'username' => $phone,
                'password_hash' => \Yii::$app->security->generatePasswordHash($phone),
                'auth_key' => \Yii::$app->security->generateRandomString(),
                'access_token' => \Yii::$app->security->generateRandomString(),
            ]);
            if ( $user->save() ) {
                $profile = $user->profile;
                $profile->family = $raw['first_name'];
                $profile->name = $raw['last_name'];
                $profile->phone = $raw['user_id'];
                return ['ok' => $profile->save(), 'data' => $user];
            }
        } else {
            $model->profile->chat_id = $raw['user_id'];
            return ['ok' => $model->profile->save(), 'data' => $model, 'message' => $model->profile->errors];
        }
//        return ['ok' => false, 'data' => null];
    }

    public function actionCheckChatId($chat_id)
    {
        sleep(3);
        $user = \app\models\User::findByChatId($chat_id);
        return ['ok' => isset($user), 'data' => $user];
    }
}