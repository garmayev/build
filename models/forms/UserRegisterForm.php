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
        ];
    }
}
