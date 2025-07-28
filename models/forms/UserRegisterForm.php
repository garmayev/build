<?php

namespace app\models\forms;

use app\models\User;
use yii\base\Model;

class UserRegisterForm extends Model
{
    public $username;
    public $email;
    public $new_password;
    public $current_password;
    public $level = User::PRIORITY_HIGH;

    private $_user;
    public $is_mail = false;
    public $referrer;

    public function rules()
    {
        return [
            [['username', 'email', 'new_password'], 'string'],
            [['username', 'email'], 'required'],
            [['username', 'email'], 'trim'],
            [['username'], 'unique', 'targetClass' => User::className(), 'targetAttribute' => ['username']],
            [['email'], 'email'],
            [['email'], 'unique', 'targetClass' => User::className(), 'targetAttribute' => ['email']],
            [['current_password', 'new_password'], 'safe'],
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
        $this->username = $this->_user->username;
        $this->email = $this->_user->email;
    }

    public function getId()
    {
        return $this->_user->id;
    }

    public function register()
    {
        $this->_user = new User();
        $this->_user->username = $this->username;
        $this->_user->email = $this->email;
        $this->_user->password_hash = \Yii::$app->security->generatePasswordHash($this->new_password);
        $this->_user->auth_key = \Yii::$app->security->generateRandomString();
        $this->_user->access_token = \Yii::$app->security->generateRandomString();
        $this->_user->status = User::STATUS_ACTIVE;
        $this->_user->referrer_id = $this->referrer;
        if ($this->_user->save()) {
            if ($this->is_mail) $this->sendMail();
            return true;
        } else {
            \Yii::error($this->_user->errors);
        }
        return false;
    }
}
