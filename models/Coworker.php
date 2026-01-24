<?php

namespace app\models;

use \yii\behaviors\BlameableBehavior;

/**
 * Class Coworker
 *
 * @property int $id
 * @property string $username
 * @property string $email
 * @property string $password_hash
 * @property string $auth_key
 * @property string $access_token
 * @property int $status
 * @property int $referrer_id
 * @property int $priority_level
 * @property int $created_at
 * @property int $updated_at
 *
 * @property string $name
 * @property string $statusName
 * @property array $statusList
 * @property float $price
 * @property float $debitAmount
 * @property float $creditAmount
 * @property int $debitHours
 * @property int $creditHours
 *
 * @property User $referrer
 * @property Profile $profile
 * @property Price[] $prices
 * @property Price $currentPrice
 * @property UserProperty[] $userProperties
 * @property Property[] $properties
 * @property Hours[] $hours
 * @property Order[] $allOrders
 * @property Order[] $activeOrders
 * @property Order[] $completedOrders
 * @property Order[] $orders
 * @property Order[] $suitableOrders
 * @property User[] $referrals
 * @property array $roles
 */
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
            [['referrer_id'], 'exist', 'targetClass' => User::class, 'targetAttribute' => 'id'],
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
            'name',
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
            'roles',
            'active_orders' => function (User $model) {
                return $model->activeOrders;
            }
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

    public function getActiveOrders()
    {
        return $this->hasMany(Order::class, ['id' => 'order_id'])
            ->viaTable('order_user', ['user_id' => 'id'])
            ->where(['in', 'order.status', [Order::STATUS_NEW, Order::STATUS_PROCESS, Order::STATUS_BUILD]]);
    }

    public function setUserProperties($data)
    {
        foreach ($this->userProperties as $property) {
            $this->unlink('userProperties', $property, true);
        }
        $db = \Yii::$app->db;
        $transaction = $db->beginTransaction();
        $this->save(false);
        try {
            if ($data) {
                foreach ($data as $item) {
                    $item['user_id'] = $this->id;
                    $property = new UserProperty();
                    if ($property->load(['UserProperty' => $item]) && $property->save()) {
                        $this->link('userProperties', $property);
                    } else {
                        \Yii::error($property->errors);
                    }
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
            ->orWhere(['priority_level' => Coworker::PRIORITY_HIGH])
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

    // Оплачено
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

    // Не Оплачено
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

    /**
     * Получает статистику по месяцам
     *
     * @param int|null $year Год (null - текущий)
     * @return array
     */
    public function getMonthlyStatistics(int $year = null): array
    {
        if ($year === null) {
            $year = date('Y');
        }

        $months = [];

        for ($month = 1; $month <= 12; $month++) {
            $startDate = date('Y-m-01', strtotime("$year-$month-01"));
            $endDate = date('Y-m-t', strtotime("$year-$month-01"));

            $months[$month] = [
                'month' => $month,
                'monthName' => date('F', strtotime("$year-$month-01")),
                'debitAmount' => $this->getDebitAmount($startDate, $endDate),
                'creditAmount' => $this->getCreditAmount($startDate, $endDate),
                'debitHours' => $this->getDebitHours($startDate, $endDate),
                'creditHours' => $this->getCreditHours($startDate, $endDate),
                'ordersCount' => $this->getCompletedOrdersCount($startDate, $endDate),
                'reportsCount' => $this->getReportsCount($startDate, $endDate),
            ];
        }

        return $months;
    }

    /**
     * Получает количество завершенных заказов за период
     *
     * @param string $startDate
     * @param string $endDate
     * @return int
     */
    public function getCompletedOrdersCount(string $startDate, string $endDate): int
    {
        return $this->getOrders()
            ->alias('o')
            ->innerJoinWith('hours h')
            ->where(['o.status' => Order::STATUS_COMPLETE])
            ->andWhere(['>=', 'h.date', $startDate])
            ->andWhere(['<=', 'h.date', $endDate])
            ->count();
    }

    /**
     * Получает количество отчетов за период
     *
     * @param string $startDate
     * @param string $endDate
     * @return int
     */
    public function getReportsCount(string $startDate, string $endDate): int
    {
        return Report::find()
            ->alias('r')
            ->innerJoin(['o' => Order::tableName()], 'o.id = r.order_id')
            ->innerJoin(['ou' => 'order_user'], 'ou.order_id = o.id')
            ->where(['ou.user_id' => $this->id])
            ->andWhere(['>=', 'r.created_at', strtotime($startDate)])
            ->andWhere(['<', 'r.created_at', strtotime($endDate . ' +1 day')])
            ->count();
    }

    /**
     * Получает сводную статистику за год
     *
     * @param int|null $year
     * @return array
     */
    public function getYearSummary(int $year = null): array
    {
        if ($year === null) {
            $year = date('Y');
        }

        $startDate = "$year-01-01";
        $endDate = "$year-12-31";

        return [
            'year' => $year,
            'totalDebitAmount' => $this->getDebitAmount($startDate, $endDate),
            'totalCreditAmount' => $this->getCreditAmount($startDate, $endDate),
            'totalDebitHours' => $this->getDebitHours($startDate, $endDate),
            'totalCreditHours' => $this->getCreditHours($startDate, $endDate),
            'totalOrders' => $this->getCompletedOrdersCount($startDate, $endDate),
            'totalReports' => $this->getReportsCount($startDate, $endDate),
            'monthlyData' => $this->getMonthlyStatistics($year),
        ];
    }

    /**
     * Получает детализацию по заказам за период
     *
     * @param string $startDate
     * @param string $endDate
     * @return array
     */
    public function getOrdersDetails(string $startDate, string $endDate): array
    {
        $orders = $this->getOrders()
            ->alias('o')
            ->with(['building', 'hours', 'reports'])
            ->where(['o.status' => Order::STATUS_COMPLETE])
            ->andWhere(['exists',
                (new \yii\db\Query())
                    ->select('*')
                    ->from(['h' => Hours::tableName()])
                    ->where('h.order_id = o.id')
                    ->andWhere(['>=', 'h.date', $startDate])
                    ->andWhere(['<=', 'h.date', $endDate])
            ])
            ->all();

        $result = [];
        foreach ($orders as $order) {
            $orderHours = 0;
            $orderAmount = 0;

            foreach ($order->hours as $hour) {
                if ($hour->date >= $startDate && $hour->date <= $endDate) {
                    $orderHours += $hour->count;
                    $orderAmount += $hour->is_payed ? $hour->debit : $hour->credit;
                }
            }

            $result[] = [
                'id' => $order->id,
                'title' => $order->title,
                'date' => date('Y-m-d', $order->date),
                'building' => $order->building ? $order->building->address : null,
                'mode' => $order->getModes()[$order->mode] ?? 'Unknown',
                'price' => $order->calculateTotalPrice(),
                'hours' => $orderHours,
                'amount' => $orderAmount,
                'reports' => count($order->reports),
                'status' => $order->statusTitle,
            ];
        }

        return $result;
    }

    public function getPriority()
    {
        $priorityList = [
            self::PRIORITY_LOW => \Yii::t('app', 'Priority low'),
            self::PRIORITY_NORMAL => \Yii::t('app', 'Priority normal'),
            self::PRIORITY_HIGH => \Yii::t('app', 'Priority high'),
        ];
        return $priorityList[$this->priority_level];
    }

    public static function getPriorityList()
    {
        return [
            self::PRIORITY_LOW => \Yii::t('app', 'Priority low'),
            self::PRIORITY_NORMAL => \Yii::t('app', 'Priority normal'),
            self::PRIORITY_HIGH => \Yii::t('app', 'Priority high'),
        ];
    }
}