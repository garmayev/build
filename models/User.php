<?php

namespace app\models;

use Yii;
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
 * @property int $status
 * @property int $referrer_id
 *
 * @property User $referrer
 * @property Profile $profile
 * @property array $statusList
 * @property string $statusName
 * @property string $name
 */
class User extends ActiveRecord implements IdentityInterface
{
    const STATUS_DISABLED = 0;
    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 2;

    public static function tableName(): string
    {
        return '{{%user}}';
    }

    public function rules(): array
    {
        return [
            [['username', 'email', 'auth_key', 'access_token', 'password_hash'], 'required'],
            [['username', 'email', 'auth_key', 'access_token'], 'string'],
            [['status'], 'integer'],
            [['status'], 'default', 'value' => self::STATUS_ACTIVE],
        ];
    }

    public function fields()
    {
        return [
            'id',
            'username',
            'email',
            'access_token',
            'status',
            'auth_key',
            'profile'
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

    public function getStatusList()
    {
        return [
            self::STATUS_DISABLED => \Yii::t('app', 'Disabled'),
            self::STATUS_ACTIVE => \Yii::t('app', 'Active'),
            self::STATUS_INACTIVE => \Yii::t('app', 'Inactive')
        ];
    }

    public function getStatusName($status = null): string
    {
        if (empty($status)) {
            $status = $this->status;
        }
        return $this->statusList[$status];
    }

    public function setProfile($data)
    {
        $db = \Yii::$app->db;
        $transaction = $db->beginTransaction();
        try {
            $profile = $this->profile;
            if (empty($profile)) {
                $profile = new Profile();
            }
            $profile->setAttributes($data);
            if ($profile->save()) {
                $this->link('profile', $profile);
                $transaction->commit();
            } else {
                $transaction->rollBack();
                \Yii::error($profile->errors);
            }
        } catch (\Exception $e) {
            $transaction->rollBack();
            \Yii::error($e);
            throw $e;
        }
    }

    public function getName(): string
    {
        return $this->profile->fullName !== "" ? $this->profile->fullName : $this->username;
    }

    public function getReferrer()
    {
        return $this->hasOne(User::className(), ['id' => 'referrer_id']);
    }
}
