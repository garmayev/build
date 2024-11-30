<?php

namespace app\models;

use yii\db\ActiveRecord;
use yii\web\IdentityInterface;

/**
 * @property integer $id
 * @property string $username
 * @property string $email
 * @property string $password
 * @property string $password_hash
 * @property string $auth_key
 * @property string $access_token
 * @property string $chat_id
 * @property string $device_id
 * @property integer $status
 * @property string $name
 *
 * @property Profile $profile
 */
class User extends ActiveRecord implements IdentityInterface
{
    const STATUS_DISABLED = 0;
    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 2;

    public $password;

    public static function tableName(): string
    {
        return '{{%user}}';
    }

    public function rules(): array
    {
        return [
            [['username', 'email', 'auth_key', 'access_token', 'password_hash'], 'required'],
            [['username', 'email', 'auth_key', 'access_token', 'chat_id', 'device_id'], 'string'],
            [['status'], 'integer'],
            [['status'], 'default', 'value' => self::STATUS_ACTIVE],
            [['password'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function findIdentity($id): ?User
    {
        return self::findOne($id);
    }

    /**
     * {@inheritdoc}
     */
    public static function findIdentityByAccessToken($token, $type = null): ?User
    {
        return self::findOne(['access_token' => $token]);
    }

    /**
     * Finds user by username
     *
     * @param string $username
     * @return static|null
     */
    public static function findByUsername($username): ?User
    {
        return self::findOne(['username' => $username]);
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthKey(): string
    {
        return $this->auth_key;
    }

    /**
     * {@inheritdoc}
     */
    public function validateAuthKey($auth_key): bool
    {
        return $this->auth_key === $auth_key;
    }

    /**
     * Validates password
     *
     * @param string $password password to validate
     * @return bool if password provided is valid for current user
     */
    public function validatePassword($password): bool
    {
        return \Yii::$app->security->validatePassword($password, $this->password_hash);
    }

    public function getProfile(): \yii\db\ActiveQuery
    {
        return $this->hasOne(Profile::class, ['id' => 'id']);
    }

    public function getCoworker()
    {
        return $this->hasOne(Coworker::class, ['user_id' => 'id']);
    }

    public function getName(): string
    {
        $profileName = "{$this->profile->last_name} {$this->profile->patronymic} {$this->profile->first_name}";
        if (strlen($profileName) > 2) {
            return $profileName;
        }
        return $this->username;
    }
}
