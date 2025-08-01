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
    const PRIORITY_LOW = 0;
    const PRIORITY_NORMAL = 1;
    const PRIORITY_HIGH = 2;

    const STATUS_DISABLED = 0;
    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 2;

    public function behaviors()
    {
        return [
            'blameable' => [
                'class' => BlameableBehavior::className(),
                'createdByAttribute' => 'referrer_id',
                'updatedByAttribute' => false,
            ]
        ];
    }

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
            [['priority_level'], 'default', 'value' => self::PRIORITY_HIGH],
            [['userProperties', 'price'], 'safe'],
        ];
    }

    public function fields(): array
    {
        return [
            'id',
            'username',
            'email',
            'access_token',
            'status',
            'auth_key',
            'price' => function (User $model) {
                $price = Price::find()
                    ->where(['user_id' => $model->id])
                    ->orderBy(['date' => SORT_DESC])
                    ->one();
                return $price->price ?? 0;
            },
            'priority' => function (User $model) {
                return $model->priority_level;
            },
            'profile',
            'userProperties' => function (User $model) {
                return $model->userProperties;
            },
            'hours',
            'roles'
        ];
    }

    public function afterSave($insert, $changedAttributes)
    {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            if ($insert) {
                $profile = new Profile(['id' => $this->id]);
                if ($profile->save()) {
                    $transaction->commit();
                } else {
                    \Yii::error($profile->getErrors());
                    $transaction->rollBack();
                }
            }
        } catch (\Exception $exception) {
            $transaction->rollBack();
            \Yii::error($exception->getMessage());
            throw $exception;
        }
        parent::afterSave($insert, $changedAttributes);
    }

    public function can($roleName)
    {
        $auth = Yii::$app->authManager;
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

    public static function findByChatId($chat_id): ?User
    {
        return self::find()->joinWith('profile')->where(['profile.chat_id' => $chat_id])->one();
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

    public function getPrices()
    {
        return $this->hasMany(Price::class, ['user_id' => 'id']);
    }

    public function getPrice()
    {
        return $this->getPrices()->orderBy(['date' => SORT_DESC])->one();
    }
    public function setPrice($value)
    {
        $transaction = Yii::$app->db->beginTransaction();
        $model = Price::find()
            ->where(['user_id' => $this->id])
            ->andWhere(['date' => date('Y-m-d')])
            ->one();
        if (empty($model)) {
            try {
                $model = new Price(['price' => $value, 'user_id' => $this->id, 'date' => date('Y-m-d')]);
                if ($model->save()) {
                    \Yii::error("Price is saved");
                    $transaction->commit();
                } else {
                    $transaction->rollBack();
                    \Yii::error($model->errors);
                }
            } catch (\Exception $exception) {
                $transaction->rollBack();
                \Yii::error($exception->getMessage());
                throw $exception;
            }
        } else {
            try {
                $model->price = $value;
                if ($model->save()) {
                    $transaction->commit();
                } else {
                    \Yii::error($model->errors);
                    $transaction->rollBack();
                }
            } catch (\Exception $exception) {
                \Yii::error($exception->getMessage());
            }
        }
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

    public function getUserProperties()
    {
        return $this->hasMany(UserProperty::class, ['user_id' => 'id']);
    }

    public function getProperties()
    {
        return $this->hasMany(Property::class, ['id' => 'property_id'])->via('userProperties');
    }

    public function getOrders()
    {
        return $this->hasMany(Order::class, ['id' => 'order_id'])->viaTable('order_user', ['user_id' => 'id']);
    }

    public function setUserProperties($data)
    {
        foreach ($this->userProperties as $property) {
            $this->unlink('userProperties', $property, true);
        }
        $db = \Yii::$app->db;
        $transaction = $db->beginTransaction();
        try {
            foreach ($data as $item) {
                $property = new UserProperty();
                if ($property->load(['UserProperty' => $item]) && $property->save()) {
                    $this->link('userProperties', $property);
                }
            }
            $transaction->commit();
        } catch (\Exception $exception) {
            $transaction->rollBack();
            \Yii::error($exception);
            throw $exception;
        }
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

    public function getName(): string
    {
        return $this->profile->fullName !== "" ? $this->profile->fullName : $this->username;
    }

    public function getReferrer()
    {
        return $this->hasOne(User::className(), ['id' => 'referrer_id']);
    }

    public function getReferrals()
    {
        return User::find()
            ->where(['referrer_id' => $this->id])
            ->orWhere(['priority_level' => User::PRIORITY_LOW])
            ->all();
    }

    public function getRoles()
    {
        return \Yii::$app->authManager->getRolesByUser($this->id);
    }

    public function getHours()
    {
        return $this->hasMany(Hours::class, ['user_id' => 'id']);
    }

    public function getHoursByMonth($startDate, $finishDate)
    {
        $query = $this->hasMany(Hours::class, ['user_id' => 'id']);
        if (!empty($startDate)) {
            $query->andWhere(['>=', 'date', $startDate]);
        }
        if (!empty($finishDate)) {
            $query->andWhere(['<=', 'date', $finishDate]);
        }
        return $query->all();
    }

    public function loadApi($data)
    {
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            $this->username = $data['username'];
            $this->email = $data['email'];
            $this->status = $data['status'];
            $this->password_hash = \Yii::$app->security->generatePasswordHash($data['password']);
            $this->auth_key = \Yii::$app->security->generateRandomString();
            $this->access_token = \Yii::$app->security->generateRandomString();
            if ($this->validate() && $this->save()) {
                $transaction->commit();
                $authManager = \Yii::$app->authManager;
                $role = $authManager->getRole($data['role']);
                $authManager->assign($role, $this->id);
                return true;
            } else {
                $transaction->rollBack();
                \Yii::error($this->getErrors());
            }
        } catch (\Exception $exception) {
            $transaction->rollBack();
            \Yii::error($exception);
        }
        return false;
    }

    public function getSuitableOrders()
    {
        /**
         * @var Requirement[] $requirements
         */
        $userId = $this->id;
        return Order::find()
            ->joinWith(['requirements' => function ($query) use ($userId) {
                $query->alias('req');
            }])
            ->leftJoin(
                'user_property up',
                'up.property_id = req.property_id 
             AND up.dimension_id = req.dimension_id 
             AND up.user_id = :userId',
                [':userId' => $userId]
            )
            ->groupBy('order.id')
            ->having([
                'or',
                [
                    'and',
                    'COUNT(req.id) > 0', // Есть требования
                    'SUM(CASE 
                        WHEN (req.type = \'less\' AND up.value <= req.value) THEN 0
                        WHEN (req.type = \'more\' AND up.value >= req.value) THEN 0
                        WHEN (req.type = \'equal\' AND up.value = req.value) THEN 0
                        WHEN (req.type = \'not-equal\' AND up.value != req.value) THEN 0
                        ELSE 1 
                    END) = 0'
                ],
                [
                    'and',
                    'COUNT(req.id) = 0' // Нет требований
                ]
            ])
            ->andWhere(['status' => Order::STATUS_NEW])
            ->andWhere(['not in', 'order.id', \yii\helpers\ArrayHelper::map($this->orders, 'id', 'id')])
            ->all();
    }

    public function getDebitAmount($startDate, $finishDate)
    {
        $result = 0;
        $hours = Hours::find()
            ->andWhere(['user_id' => $this->id]);
        if (!empty($startDate)) {
            $hours->andWhere(['>=', 'date', $startDate]);
        }
        if (!empty($finishDate)) {
            $hours->andWhere(['<=', 'date', $finishDate]);
        }
        foreach ($hours->all() as $hour) {
            $result += $hour->debit;
        }
        return $result;
    }

    public function getCreditAmount($startDate, $finishDate)
    {
        $result = 0;
        $hours = Hours::find()
            ->andWhere(['user_id' => $this->id]);
        if (!empty($startDate)) {
            $hours->andWhere(['>=', 'date', $startDate]);
        }
        if (!empty($finishDate)) {
            $hours->andWhere(['<=', 'date', $finishDate]);
        }
        foreach ($hours->all() as $hour) {
            $result += $hour->credit;
        }
        return $result;
    }

    public function getDebitHours($startDate, $finishDate)
    {
        $result = 0;
        $hours = Hours::find()
            ->andWhere(['user_id' => $this->id]);
        if (!empty($startDate)) {
            $hours->andWhere(['>=', 'date', $startDate]);
        }
        if (!empty($finishDate)) {
            $hours->andWhere(['<=', 'date', $finishDate]);
        }
        foreach ($hours->all() as $hour) {
            $result += $hour->is_payed ? $hour->count : 0;
        }
        return $result;
    }

    public function getCreditHours($startDate, $finishDate)
    {
        $result = 0;
        $hours = Hours::find()
            ->andWhere(['user_id' => $this->id]);
        if (!empty($startDate)) {
            $hours->andWhere(['>=', 'date', $startDate]);
        }
        if (!empty($finishDate)) {
            $hours->andWhere(['<=', 'date', $finishDate]);
        }
        foreach ($hours->all() as $hour) {
            $result += $hour->is_payed ? 0 : $hour->count;
        }
        return $result;
    }
}
