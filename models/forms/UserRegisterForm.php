<?php

namespace app\models\forms;

use app\models\Coworker;
use app\models\Order;
use app\models\Profile;
use app\models\User;
use floor12\phone\PhoneValidator;
use yii\base\Model;

class UserRegisterForm extends Model
{
    public $username;
    public $email;
    public $priority;

    public $family;
    public $name;
    public $surname;
    public $birthday;
    public $phone;

    public $properties;

    private $_user;
    private $_profile;
    public $is_mail = false;
    public $referrer;

    public function rules()
    {
        return [
            [['username', 'email', 'family', 'name', 'surname'], 'string'],
            [['username', 'email', 'family', 'phone'], 'required'],
            [['username', 'email', 'family', 'name', 'surname'], 'trim'],
            [['username'], 'unique', 'targetClass' => User::className(), 'targetAttribute' => ['username']],
            [['email'], 'email'],
            [['email'], 'unique', 'targetClass' => User::className(), 'targetAttribute' => ['email']],
            [['priority'], 'in', 'range' => [Coworker::PRIORITY_LOW, Coworker::PRIORITY_NORMAL, Coworker::PRIORITY_HIGH]],
            [['phone'], PhoneValidator::class],
            [['birthday'], 'date', 'format' => 'php:Y-m-d'],
            [['properties'], 'safe']
        ];
    }

    public function update()
    {
        $this->_user = User::findOne(['email' => $this->email]);
        if ($this->_user && $this->current_password && $this->_user->validatePassword($this->current_password)) {
            $this->_user->username = $this->username;
            $this->_user->email = $this->email;
            if ($this->new_password) {
                $this->_user->password_hash = \Yii::$app->security->generatePasswordHash($this->new_password);
            }
            return $this->_user->save();
        } else {
            $this->_user = new User();
            $data = explode('@', $this->email);
            $this->_user->username = $data[0];
            $this->_user->email = $this->email;
            $this->_user->access_token = \Yii::$app->security->generateRandomString();
            $this->_user->auth_key = \Yii::$app->security->generateRandomString();
            $this->new_password = \Yii::$app->security->generateRandomString(6);
            $this->_user->password_hash = \Yii::$app->security->generatePasswordHash($this->new_password);
            if ($this->_user->save()) {
                $this->sendMail();
                return true;
            } else {
                \Yii::error($this->_user->getErrors());
            }
        }
        return false;
    }

    private function sendMail()
    {
        \Yii::$app->mailer
            ->compose('coworker/register', ['user' => $this])
            ->setFrom(['build@amgcompany.ru' => 'build@amgcompany.ru'])
            ->setTo($this->_user->email)
            ->setSubject(\Yii::$app->name . ' robot')
            ->send();
    }

    public function findUser($id)
    {
        $this->_user = User::findOne($id);
        if ( $this->_user ) {
            $this->username = $this->_user->username;
            $this->email = $this->_user->email;
        }
    }

    public function attributeLabels()
    {
        return [
            'username' => \Yii::t('app', 'Username'),
            'email' => \Yii::t('app', 'Email'),
            'family' => \Yii::t('app', 'Family'),
            'name' => \Yii::t('app', 'Name'),
            'surname' => \Yii::t('app', 'Surname'),
            'birthday' => \Yii::t('app', 'Birthday'),
            'phone' => \Yii::t('app', 'Phone'),
            'properties' => \Yii::t('app', 'Properties'),
            'priority' => \Yii::t('app', 'Priority'),
        ];
    }

    public function getIsNewRecord()
    {
        return !isset($this->_user);
    }

    public function getId()
    {
        return $this->_user->id;
    }

    public function register($user_id)
    {
        return $this->createProfile($user_id);
    }

    private function createUser($user_id = null)
    {
        if (isset($user_id)) {
            $this->_user = Coworker::findOne($user_id);
        } else {
            $this->_user = new Coworker();
        }

        $this->_user->load([
            "username" => $this->username,
            "email" => $this->email,
            "password_hash" => \Yii::$app->security->generatePasswordHash($this->email),
            "auth_key" => \Yii::$app->security->generateRandomString(),
            "access_token" => \Yii::$app->security->generateRandomString(),
            "status" => User::STATUS_ACTIVE,
            "referrer_id" => \Yii::$app->user->id,
            "priority_level" => $this->priority,
            "userProperties" => $this->properties,
        ], '');

        if ($this->_user->save()) {
            return true;
        } else {
            \Yii::error($this->_user->getErrors());
            return false;
        }
    }

    private function createProfile($user_id)
    {
        if (!isset($this->_user)) {
            $this->createUser($user_id);
        } else {
            $this->_user = User::findOne($user_id);
        }
        if ($this->_user->profile) {
            $this->_profile = $this->_user->profile;
        } else {
            $this->_profile = new Profile();
        }
        $this->_profile->load([
            'family' => $this->family,
            'name' => $this->name,
            'surname' => $this->surname,
            'phone' => $this->phone,
            'birthday' => $this->birthday,
            'user_id' => $this->_user->id,
        ], '');
        if ($this->_profile->save()) {
            return $this->_user->link('profile', $this->_profile);
        }
        \Yii::error($this->_profile->getErrors());
        return false;
    }

    public function restore($id)
    {
        $this->_user = Coworker::findOne($id);
        if (!$this->_user) {
            $this->_profile = new Profile();
            $this->properties = [];
            return;
        } else {
            $this->_profile = $this->_user->profile;
        }
        $this->username = $this->_user->username;
        $this->email = $this->_user->email;
        $this->family = $this->_profile->family;
        $this->name = $this->_profile->name;
        $this->surname = $this->_profile->surname;
        $this->phone = $this->_profile->phone;
        $this->birthday = $this->_profile->birthday;
        $this->priority = $this->_user->priority_level;
        $this->properties = $this->_user->userProperties;
    }
}
