<?php

namespace app\models;

use \yii\behaviors\BlameableBehavior;

class Coworker extends User 
{
    const PRIORITY_LOW = 0;
    const PRIORITY_NORMAL = 1;
    const PRIORITY_HIGH = 2;

    public function behaviors()
    {
        return [
            'blameable' => [
                'class' => BlameableBehavior::class,
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
        return array_merge(parent::rules(), [
            [['priority_level'], 'default', 'value' => self::PRIORITY_HIGH],
            [['userProperties', 'price'], 'safe'],
            [['referrer_id'], 'exist', 'targetClass' => self::class, 'targetAttribute' => 'id'],
        ]);
    }

    public static function find()
    {
        $ids = \Yii::$app->authManager->getUserIdsByRole('employee');
        return parent::find()->alias('coworker')->where(['in', 'coworker.id', $ids]);
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
        if ($insert) {
            $auth = \Yii::$app->authManager;
            $auth->assign($auth->getRole('employee'), $this->id);
        }
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
        $transaction = \Yii::$app->db->beginTransaction();
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
        return $this->hasMany(Order::class, ['id' => 'order_id'])
            ->viaTable('order_user', ['user_id' => 'id'])
            ->where(['in', 'order.status', [Order::STATUS_NEW, Order::STATUS_PROCESS, Order::STATUS_BUILD]])
            ->andWhere(["or", ["order.created_by" => $this->referrer_id], ["order.created_by" => $this->id]]);
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
//            \Yii::error(\Yii::$app->user->isGuest);
            if (!\Yii::$app->user->isGuest) {
                $this->referrer_id = \Yii::$app->user->getId();
            } else {
                $this->referrer_id = 54;
            }
            if ($this->save()) {
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
            ->andWhere(['or', ['order.created_by' => $this->referrer_id], ['order.created_by' => $this->id]]);
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