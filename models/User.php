<?php

namespace app\models;

use Yii;
use yii\behaviors\BlameableBehavior;
use yii\db\ActiveQuery;
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
 * @property UserProperty[] $userProperties
 * @property Property[] $properties
 * @property Order[] $suitableOrders
 * @property Order[] $orders
 * @property Price[] $prices
 * @property Hours[] $hours
 * @property User[] $referrals
 * @property Profile $profile
 * @property string $statusName
 * @property string $name
 * @property array $statusList
 * @property Price $price
 * @property float $debitAmount
 * @property float $creditAmount
 * @property User $referrer
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

    public function rules()
    {
        return [
            [['username', 'email', 'auth_key', 'access_token', 'password_hash'], 'required'],
            [['username', 'email', 'auth_key', 'access_token'], 'string'],
            [['status'], 'integer'],
            [['status'], 'default', 'value' => self::STATUS_ACTIVE],
        ];
    }

    public function can($roleName)
    {
        $auth = \Yii::$app->authManager;
        $roles = $auth->getRolesByUser($this->getId());
        foreach ($roles as $role) {
            if ($roleName == $role->name) {
                return true;
            }
        }
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public static function findIdentity($id): ?self
    {
        return self::findOne($id);
    }

    /**
     * {@inheritdoc}
     */
    public static function findIdentityByAccessToken($token, $type = null): ?self
    {
        // Используем auth_key как токен доступа
        return self::findOne(['auth_key' => $token]);
    }

    /**
     * {@inheritdoc}
     */
    public function getId(): int
    {
        return (int) $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthKey(): string
    {
        return (string) $this->auth_key;
    }

    /**
     * {@inheritdoc}
     */
    public function validateAuthKey($authKey): bool
    {
        return $this->getAuthKey() === $authKey;
    }

    public function getFullName()
    {
        $name = ltrim("{$this->profile->family} {$this->profile->name} {$this->profile->surname}");
        return $name ?? $this->username;
    }


    public function getProfile(): \yii\db\ActiveQuery
    {
        return $this->hasOne(Profile::class, ['id' => 'id']);
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

}
