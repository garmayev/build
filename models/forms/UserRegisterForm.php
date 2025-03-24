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

    private $_user;

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
        $this->_user = User::findByUsername($this->username);
        \Yii::error( $this->username );
        \Yii::error( $this->_user->validatePassword($this->current_password) );
        if ($this->_user && $this->current_password && $this->_user->validatePassword($this->current_password)) {
            $this->_user->username = $this->username;
            $this->_user->email = $this->email;
            if ($this->new_password) {
                $this->_user->password_hash = \Yii::$app->security->generatePasswordHash($this->new_password);
            }
            return $this->_user->save();
        }
        return false;
    }

    public function findUser($id)
    {
        $this->_user = User::findOne($id);
        $this->username = $this->_user->username;
        $this->email = $this->_user->email;
    }
}
