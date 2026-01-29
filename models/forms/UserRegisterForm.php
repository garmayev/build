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
    public $_is_update = false;
    public $is_mail = false;
    public $referrer;

    public function rules()
    {
        return [
            [['username', 'email', 'family', 'name', 'surname'], 'string'],
            [['username', 'email', 'family', 'phone'], 'required'],
            [['username', 'email', 'family', 'name', 'surname'], 'trim'],

            // Правило уникальности для username с исключением текущего пользователя
            [
                ['username'],
                'unique',
                'targetClass' => User::class,
                'targetAttribute' => ['username'],
                'filter' => function($query) {
                    if ($this->_is_update && $this->_user) {
                        // Исключаем текущего пользователя из проверки
                        $query->andWhere(['not', ['id' => $this->_user->id]]);
                    }
                },
                'message' => \Yii::t('app', 'This username has already been taken.')
            ],

            // Правило уникальности для email с исключением текущего пользователя
            [
                ['email'],
                'unique',
                'targetClass' => User::class,
                'targetAttribute' => ['email'],
                'filter' => function($query) {
                    if ($this->_is_update && $this->_user) {
                        // Исключаем текущего пользователя из проверки
                        $query->andWhere(['not', ['id' => $this->_user->id]]);
                    }
                },
                'message' => \Yii::t('app', 'This email address has already been taken.')
            ],

            [['email'], 'email'],
            [['priority'], 'in', 'range' => [Coworker::PRIORITY_LOW, Coworker::PRIORITY_NORMAL, Coworker::PRIORITY_HIGH]],
            [['phone'], PhoneValidator::class],
            [['birthday'], 'date', 'format' => 'php:Y-m-d'],
            [['properties'], 'safe']
        ];
    }

    // UserRegisterForm.php
    public function update()
    {
        // Если у нас уже есть _user (при восстановлении данных), используем его
        if (!$this->_user) {
            \Yii::error('User not found for update');
            return false;
        }

        $transaction = \Yii::$app->db->beginTransaction();
        try {
            // Обновляем основные данные пользователя
            $this->_user->username = $this->username;
            $this->_user->email = $this->email;
            $this->_user->priority_level = $this->priority;

            // Обновляем свойства пользователя, если они есть
            if ($this->properties !== null) {
                $this->_user->setUserProperties($this->properties);
            }

            // Сохраняем пользователя
            if (!$this->_user->save()) {
                $transaction->rollBack();
                \Yii::error('Failed to save user: ' . json_encode($this->_user->getErrors()));
                return false;
            }

            // Обновляем профиль
            $profile = $this->_user->profile;
            if (!$profile) {
                $profile = new Profile();
                $profile->user_id = $this->_user->id;
            }

            $profile->family = $this->family;
            $profile->name = $this->name;
            $profile->surname = $this->surname;
            $profile->phone = $this->phone;
            $profile->birthday = $this->birthday;

            if (!$profile->save()) {
                $transaction->rollBack();
                \Yii::error('Failed to save profile: ' . json_encode($profile->getErrors()));
                return false;
            }

            $transaction->commit();
            return true;

        } catch (\Exception $e) {
            $transaction->rollBack();
            \Yii::error('Exception in UserRegisterForm::update(): ' . $e->getMessage());
            return false;
        }
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

        if (!$this->_is_update) {
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
        }

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
        $this->_is_update = true;

        // Находим пользователя
        $this->_user = Coworker::findOne($id);

        if (!$this->_user) {
            \Yii::error("Coworker with id {$id} not found");
            $this->_profile = new Profile();
            $this->properties = [];
            return;
        }

        // Получаем профиль (если нет - создаем новый)
        $this->_profile = $this->_user->profile;
        if (!$this->_profile) {
            $this->_profile = new Profile();
            $this->_profile->user_id = $this->_user->id;
        }

        // Заполняем данные формы
        $this->username = $this->_user->username;
        $this->email = $this->_user->email;
        $this->family = $this->_profile->family;
        $this->name = $this->_profile->name;
        $this->surname = $this->_profile->surname;
        $this->phone = $this->_profile->phone;
        $this->birthday = $this->_profile->birthday;
        $this->priority = $this->_user->priority_level;

        // Получаем свойства пользователя
        $this->properties = [];
        $userProperties = $this->_user->userProperties;
        if ($userProperties) {
            foreach ($userProperties as $userProperty) {
                $this->properties[] = $userProperty;
            }
        }
    }
}